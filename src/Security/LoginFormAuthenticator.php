<?php

namespace App\Security;

use App\Controller\Core\ConfigLoaders;
use App\Entity\User;
use App\Services\Core\Translator;
use App\Services\Session\ExpirableSessionsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE     = 'login';
    public const LOGIN_CHECK_URI = "/login_check"; // this is part of symfony logic

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private Translator  $translator;

    /**
     * @var ExpirableSessionsService $expirable_sessions_service
     */
    private ExpirableSessionsService $expirable_sessions_service;

    /**
     * @var ConfigLoaders $config_loaders
     */
    private ConfigLoaders $config_loaders;

    public function __construct(
        EntityManagerInterface       $entityManager,
        UrlGeneratorInterface        $urlGenerator,
        CsrfTokenManagerInterface    $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder,
        Translator                   $translator,
        ExpirableSessionsService     $expirable_sessions_service,
        ConfigLoaders                $config_loaders
    )
    {
        $this->entityManager              = $entityManager;
        $this->urlGenerator               = $urlGenerator;
        $this->csrfTokenManager           = $csrfTokenManager;
        $this->passwordEncoder            = $passwordEncoder;
        $this->translator                 = $translator;
        $this->config_loaders             = $config_loaders;
        $this->expirable_sessions_service = $expirable_sessions_service;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username']]);

        if (!$user) {
            $message = $this->translator->translate('login.errors.userNotFound');
            throw new CustomUserMessageAuthenticationException($message);
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $is_password_valid = $this->passwordEncoder->isPasswordValid($user, $credentials['password']);

        if( !$is_password_valid ){
            $message = $this->translator->translate('login.errors.invalidPassword');
            throw new BadCredentialsException($message);
        }

        return true;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse|Response|null
     * @throws \Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // save expirable session to auto logout user once it expires
        $user_login_session_lifetime = $this->config_loaders->getConfigLoaderSession()->getUserLoginLifetime();
        $this->expirable_sessions_service->addSessionLifetime(ExpirableSessionsService::KEY_SESSION_USER_LOGIN_LIFETIME, $user_login_session_lifetime);

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('dashboard'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
