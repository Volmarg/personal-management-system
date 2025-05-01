<?php

namespace App\Services\Security;

use App\Entity\User;
use App\Listeners\Bundles\LexitJwtAuthentication\JwtCreatedListener;
use App\Repository\UserRepository;
use App\Services\Core\Logger;
use DateTime;
use Doctrine\DBAL\LockMode;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;
use TypeError;

class JwtAuthenticationService
{
    /**
     * Must be in sync with:
     * - `lexik_jwt_authentication.user_identity_field`
     * - `security.providers.app_user_provider.entity.property`
     */
    const JWT_KEY_EMAIL = "email";

    const JWT_KEY_USERNAME = "username";
    const JWT_KEY_EXPIRES  = "exp";

    public const JWT_IS_SYSTEM_LOCKED = "isSystemLocked";

    const HEADER_AUTHENTICATION = "authorization"; // must be lowercase, due to how php turns all headers keys to lowercase
    const HEADER_KEY_BEARER     = "Bearer";        // remains ucfirst to properly extract token from header

    /**
     * @var UserRepository $userRepository
     */
    private UserRepository $userRepository;

    /**
     * @var JWTEncoderInterface JWTEncoderInterface $JWTEncoder
     */
    private JWTEncoderInterface $jwtEncoder;

    /**
     * @var JWTTokenManagerInterface $jwtManager
     */
    private JWTTokenManagerInterface $jwtManager;

    /**
     * @param UserRepository           $userRepository
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @param Logger                   $logger
     */
    public function __construct(
        UserRepository           $userRepository,
        JWTTokenManagerInterface $JWTTokenManager,
        private readonly Logger  $logger,
    )
    {
        $this->jwtManager     = $JWTTokenManager;
        $this->userRepository = $userRepository;
    }

    /**
     * Will extract jwt token either from headers of request or query params
     * If token is present then it gets returned, otherwise {@see null} gets returned
     *
     * Header is the main source of token as it comes from 95% requests, only some special one
     * are getting stored inside the query (like one time tokens, or links which basically must have token to work).
     *
     * @return string|null
     */
    public function extractJwtFromRequest(): ?string {
        $request  = Request::createFromGlobals();
        $jwtToken = null;

        if ($request->headers->has(self::HEADER_AUTHENTICATION)) {
            $authorizationHeader = $request->headers->get(self::HEADER_AUTHENTICATION);
            $jwtToken            = preg_replace("#" . self::HEADER_KEY_BEARER ."[ ]?#", "", $authorizationHeader);
        }

        if (
                empty($jwtToken)
            &&  $request->query->has("token")
        ) {
            $jwtToken = $request->query->get('token');
        }

        if (empty($jwtToken)) {
            return null;
        }

        return $jwtToken;
    }

    /**
     * Will attempt to get user from token in request
     *
     * @return User
     */
    public function getUserFromRequest(): User {
        $jwtToken = $this->extractJwtFromRequest();
        if (empty($jwtToken)) {
            throw new AccessDeniedException("Request is missing token. Searched in header: " . self::HEADER_AUTHENTICATION . ", and in query param");
        }

        $user = $this->getUserForToken($jwtToken);
        return $user;
    }

    /**
     * Handles refreshing jwt token. Token will be refreshed under certain rules, otherwise the same token gets returned.
     *
     * @param string $tokenRaw
     *
     * @return string
     *
     * @throws JWTDecodeFailureException
     */
    public function handleJwtTokenRefresh(string $tokenRaw): string
    {
        $jwtPayload = $this->getPayloadFromToken($tokenRaw);
        $userRoles  = $jwtPayload['roles'];

        /**
         * This role is required for each user, so if the token does not contain it then it's some special purpose token,
         * and it should not be refreshed
         */
        if (!in_array(User::ROLE_USER, $userRoles)) {
            return $tokenRaw;
        }

        return $this->refreshJwtToken($tokenRaw);
    }

    /**
     * Prepares special jwt token with given roles in, if You want to create normal token with all the roles user have
     * then use {@see JwtAuthenticationService::buildTokenForUser()}.
     *
     * This kind of tokens might be handy when there is some need to generate token for certain actions only,
     * where given token should be used precisely for given thing (like reset password), without letting user
     * use the token on the page when logged-in, thus it's possible to strip out the base {@see User::ROLE_USER} etc.
     *
     * @param User  $user
     * @param array $rolesAndRights
     * @param bool  $includeBaseRole - {@see User::ROLE_USER}
     *
     * @return string
     */
    public function buildWithRolesForUser(User $user, array $rolesAndRights, bool $includeBaseRole = true): string
    {
        $originalRoles = $user->getRoles();
        if (!$includeBaseRole) {
            $user->setGetRoleGuaranteeRoleUser(false);
        }

        $user->setRoles($rolesAndRights);
        $token = $this->buildTokenForUser($user, []);

        // set the roles back else user gets updated and got wrong roles in DB
        $user->setRoles($originalRoles);

        return $token;
    }

    /**
     * Will build one time token for user
     *
     * @param User  $user
     * @param array $extraPayload
     * @param bool  $endless
     *
     * @return string
     */
    public function buildTokenForUser(User $user, array $extraPayload = [], bool $endless = false): string
    {
        if ($endless) {
            $tokenExpirationTimestamp = (new DateTime())->modify("+99 YEAR")->getTimestamp();
            $extraPayload['exp']      = $tokenExpirationTimestamp;
        }

        $token = $this->jwtManager->createFromPayload($user, $extraPayload);

        return $token;
    }

    /**
     * Check is user is granted any of given rights or roles
     *
     * @param array       $rolesAndRights
     * @param string|null $jwtToken
     *
     * @return bool
     */
    public function isAnyGrantedToUser(array $rolesAndRights, ?string $jwtToken = null): bool {
        if (!empty($jwtToken)) {
            $user = $this->getUserForToken($jwtToken);
        } else {
            $user = $this->getUserFromRequest();
        }

        $matchingPrivileges = array_intersect($rolesAndRights, $user->getRoles());

        return !empty($matchingPrivileges);
    }

    /**
     * Will check if token is valid
     *
     * @param string $rawToken
     * @return bool
     */
    public function isTokenValid(string $rawToken): bool
    {
        try {
            $this->jwtManager->parse($rawToken);
            return true;
        } catch (Exception|TypeError $e) {
            $this->logger->getLogger()->error("Could not parse the jwt token", [
                "exceptionMessage" => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param string $rawToken
     * @param bool $muteException
     * @return User|null
     */
    public function getUserForToken(string $rawToken, bool $muteException = true): User|null
    {
        $user = null;

        try {
            $payload   = $this->getPayloadFromToken($rawToken);
            $userEmail = $payload[self::JWT_KEY_EMAIL];
            $user      = $this->userRepository->findOneByEmail($userEmail);
        } catch (Exception|TypeError $e) {
            if (!$muteException) {
                throw $e;
            }
            // not handling as user might not been set - maybe faked token
        }

        return $user;
    }

    /**
     * There are so many jwt based exceptions that this method return information if exception is jwt authentication based
     *
     * @param Exception $e
     * @return bool
     */
    public static function isJwtTokenException(Throwable $e){
        if(
                $e instanceof ExpiredTokenException
            ||  $e instanceof InvalidPayloadException
            ||  $e instanceof InvalidTokenException
            ||  $e instanceof JWTDecodeFailureException
            ||  $e instanceof JWTEncodeFailureException
            ||  $e instanceof MissingTokenException
            ||  $e instanceof UserNotFoundException
        ){
            return true;
        }

        return false;
    }

    /**
     * Will return the payload from token
     *
     * @param string $rawToken
     * @return array
     * @throws JWTDecodeFailureException
     */
    public function getPayloadFromToken(string $rawToken): array
    {
        $token = new JWTUserToken();
        $token->setRawToken($rawToken);

        $jwtPayload = $this->jwtManager->decode($token);
        return $jwtPayload;
    }

    /**
     * Will check if token is expired
     * @throws JWTDecodeFailureException
     */
    public function isTokenExpired(string $token): bool
    {
        try {
            $expirationEpoch = $this->getTokenExpirationTimestamp($token);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), "Expired JWT Token")) {
                return true;
            }

            throw $e;
        }

        return (new DateTime())->getTimestamp() > $expirationEpoch;
    }

    /**
     * Check if system is locked or not for user
     *
     * @return bool
     *
     * @throws JWTDecodeFailureException
     */
    public function isSystemLocked(): bool
    {
        $token = $this->extractJwtFromRequest();
        if (!$token) {
            return false;
        }

        $payload = $this->getPayloadFromToken($token);
        return $payload[self::JWT_IS_SYSTEM_LOCKED] ?? true;
    }

    /**
     * Returns the token expiration timestamp
     *
     * @param string $token
     * @return string
     * @throws JWTDecodeFailureException
     */
    public function getTokenExpirationTimestamp(string $token): string
    {
        $payload         = $this->getPayloadFromToken($token);
        $expirationEpoch = $payload[self::JWT_KEY_EXPIRES];

        return $expirationEpoch;
    }

    /**
     * Takes the jwt token, refreshes it and sends it back
     * Other fields are added in: {@see JwtCreatedListener}
     *
     * @param string $tokenRaw
     * @return string
     */
    private function refreshJwtToken(string $tokenRaw): string
    {
        try {
            $jwtPayload = $this->getPayloadFromToken($tokenRaw);
            $userId     = $jwtPayload[JwtCreatedListener::JWT_KEY_USER_ID];
            $user       = $this->userRepository->find($userId, LockMode::NONE); // always get fresh user state

            $refreshedJwtToken = $this->jwtManager->createFromPayload($user, []);
        } catch (Exception|TypeError $e) {
            $this->logger->logException($e, [
                "jwtToken" => $tokenRaw,
            ]);
            throw $e;
        }
        return $refreshedJwtToken;
    }
}