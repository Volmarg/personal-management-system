<?php

namespace App\Security;

use App\Attribute\JwtAuthenticationDisabledAttribute;
use App\Response\Base\BaseResponse;
use App\Services\Attribute\AttributeReaderService;
use App\Services\Security\JwtAuthenticationService;
use App\Traits\ExceptionLoggerAwareTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Extends the Lexit Bundle authentication logic
 */
class LexitBundleJwtTokenAuthenticator extends JWTTokenAuthenticator
{
    use ExceptionLoggerAwareTrait;

    public function __construct(
        JWTTokenManagerInterface                $jwtManager,
        EventDispatcherInterface                $dispatcher,
        TokenExtractorInterface                 $tokenExtractor,
        TokenStorageInterface                   $preAuthenticationTokenStorage,
        private readonly AttributeReaderService $attributeReaderService,
        private readonly LoggerInterface        $logger,
        private readonly JwtAuthenticationService $jwtAuthenticationService
    ) {
        parent::__construct($jwtManager, $dispatcher, $tokenExtractor, $preAuthenticationTokenStorage);
    }

    /**
     * @param Request $request
     * @return bool
     * @throws ReflectionException
     */
    public function supports(Request $request): bool
    {
       if(
                UriAuthenticator::isUriExcludedFromAuthenticationByRegex() // must be first due to profiler falling in this case yet crashes for other checks (Symfony issue)
           ||   $this->attributeReaderService->hasUriAttribute($request->getRequestUri(), JwtAuthenticationDisabledAttribute::class)
       ){
            return false;
        }

        /**
         * Returns false if authentication header is missing, thus if any route must be tested then either use request test tool,
         * or add the {@see JwtAuthenticationDisabledAttribute} on the target route
         */
        return parent::supports($request);
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $authException
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $authException): JsonResponse
    {
        $apiResponse = new BaseResponse();
        if (JwtAuthenticationService::isJwtTokenException($authException)) {
            $message = $authException->getMessage() ?: $authException::class;
            $apiResponse->setCode(Response::HTTP_UNAUTHORIZED);
            $apiResponse->setMessage($message);
        } else {

            $apiResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $this->logException($authException);
        }
        return $apiResponse->toJsonResponse();
    }

    /**
     * @param Request $request
     *
     * @return PreAuthenticationJWTUserToken
     *
     * @throws JWTDecodeFailureException
     */
    public function getCredentials(Request $request): PreAuthenticationJWTUserToken
    {
        $jwtToken = $this->jwtAuthenticationService->extractJwtFromRequest();
        if (empty($jwtToken)) {
            throw new InvalidTokenException('JWT Token not found.', Response::HTTP_BAD_REQUEST);
        }

        $isTokenValid = $this->jwtAuthenticationService->isTokenValid($jwtToken);
        if (!$isTokenValid) {
            throw new InvalidTokenException("This jwt token is not valid!");
        }

        $isExpired = $this->jwtAuthenticationService->isTokenExpired($jwtToken);
        if ($isExpired) {
            throw new InvalidTokenException('This jwt token is expired', Response::HTTP_BAD_REQUEST);
        }

        $user = $this->jwtAuthenticationService->getUserForToken($jwtToken);
        if (empty($user)) {
            throw new InvalidTokenException('Invalid JWT Token - no user found for this token.', Response::HTTP_BAD_REQUEST);
        }

        $preAuthToken = new PreAuthenticationJWTUserToken($jwtToken);

        try {
            $payload = $this->jwtAuthenticationService->getPayloadFromToken($jwtToken);
            if (!$payload) {
                throw new InvalidTokenException('Invalid JWT Token - could not extract payload');
            }

            $preAuthToken->setPayload($payload);
        } catch (JWTDecodeFailureException $e) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                $expiredTokenException = new ExpiredTokenException();
                $expiredTokenException->setToken($preAuthToken);
                throw $expiredTokenException;
            }

            throw new InvalidTokenException('Invalid JWT Token', Response::HTTP_BAD_REQUEST);
        }

        return $preAuthToken;
    }
}