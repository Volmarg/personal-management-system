<?php

namespace App\Listeners;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Entity\User;
use App\Services\Exceptions\SecurityException;
use App\Services\Core\Logger;
use App\Services\Session\ExpirableSessionsService;
use App\Services\Session\UserRolesSessionService;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Security layer for logging every single page call
 *  - first of all for security reason
 *  - second the request data might end up in log so if something fails during insert it might be recovered this way
 * Class OnKernelRequestListener
 */
class OnKernelRequestListener implements EventSubscriberInterface {

    const LOGGER_REQUEST_URL       = "requestUrl";
    const LOGGER_REQUEST_METHOD    = "requestMethod";
    const LOGGER_REQUEST_GET_DATA  = "requestGetData";
    const LOGGER_REQUEST_POST_DATA = "requestPostData";
    const LOGGER_REQUEST_IP        = "requestIp";
    const LOGGER_REQUEST_CONTENT   = "requestContent";
    const LOGGER_REQUEST_HEADERS   = "requestHeaders";

    const ALLOWED_REQUEST_TYPES = [
        "POST",
        "GET",
    ];

    /**
     * @var Logger $security_logger
     */
    private $security_logger;

    /**
     * @var ExpirableSessionsService $expirable_sessions_service
     */
    private $expirable_sessions_service;

    /**
     * @var UrlGeneratorInterface $url_generator
     */
    private UrlGeneratorInterface $url_generator;

    /**
     * @var Application $app
     */
    private Application $app;

    public function __construct(Logger $security_logger, ExpirableSessionsService $sessions_service, UrlGeneratorInterface  $url_generator, Application $app) {
        $this->security_logger            = $security_logger->getSecurityLogger();
        $this->expirable_sessions_service = $sessions_service;
        $this->url_generator              = $url_generator;
        $this->app                        = $app;
    }

    /**
     * @param RequestEvent $ev
     * @throws SecurityException
     * @throws Exception
     *
     */
    public function onRequest(RequestEvent $ev)
    {
        $is_system_lock_unlocked_before_handling_expiration = UserRolesSessionService::hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES);

        $this->handleSessionsLifetimes($ev);
        $this->handleLogoutUserOnExpiredLoginSession($ev);
        $this->handleTurnLockOffOnExpiredUnlockSession($ev, $is_system_lock_unlocked_before_handling_expiration);
        $this->logRequest($ev);
        $this->blockRequestTypes($ev);
        $this->blockIp($ev);
    }

    public static function getSubscribedEvents() {
        return [
          KernelEvents::REQUEST => ['onRequest']
        ];
    }

    /**
     * @param RequestEvent $event
     */
    private function logRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $method     = $request->getMethod();
        $get_data   = json_encode($request->query->all());
        $post_data  = json_encode($request->request->all());
        $ip         = $request->getClientIp();
        $content    = $request->getContent();
        $headers    = json_encode($request->headers->all());
        $url        = $request->getUri();

        $this->security_logger->info("Visited url", [
            self::LOGGER_REQUEST_URL       => $url,
            self::LOGGER_REQUEST_METHOD    => $method,
            self::LOGGER_REQUEST_GET_DATA  => $get_data,
            self::LOGGER_REQUEST_POST_DATA => $post_data,
            self::LOGGER_REQUEST_IP        => $ip,
            self::LOGGER_REQUEST_CONTENT   => $content,
            self::LOGGER_REQUEST_HEADERS   => $headers,
        ]);
    }

    /**
     * @param RequestEvent $event
     * @throws SecurityException
     * 
     */
    private function blockRequestTypes(RequestEvent $event): void
    {
        $request_method = $event->getRequest()->getMethod();

        if( !in_array($request_method, self::ALLOWED_REQUEST_TYPES) ){

            $response = new Response();
            $response->setContent("");

            $event->stopPropagation();
            $event->setResponse($response);

            $log_message       = $this->app->translator->translate("logs.security.visitedPageWithUnallowedMethod");
            $exception_message = $this->app->translator->translate('exceptions.security.youAreNotAllowedToSeeThis');

            $this->security_logger->info($log_message);
            throw new SecurityException($exception_message);
        }

    }

    /**
     * @param RequestEvent $event
     * @throws SecurityException
     * @throws Exception
     *
     */
    private function blockIp(RequestEvent $event): void
    {
        $restricted_ips = $this->app->configLoaders->getConfigLoaderSecurity()->getRestrictedIps();
        $request        = $event->getRequest();
        $ip             = $request->getClientIp();

        if( empty($restricted_ips) ){
            return;
        }

        if( !in_array($ip, $restricted_ips) ){
            $response = new Response();
            $response->setContent("");

            $event->stopPropagation();
            $event->setResponse($response);

            $log_message       = $this->app->translator->translate("logs.security.visitedPageWithUnallowedIp");
            $exception_message = $this->app->translator->translate('exceptions.security.youAreNotAllowedToSeeThis');

            $this->security_logger->info($log_message, [
                "ip" => $ip,
            ]);
            throw new SecurityException($exception_message);
        }

    }

    /**
     * This method will either extend session lifetime, or invalidate data in session after given idle time
     * @param RequestEvent $event
     * @throws Exception
     */
    private function handleSessionsLifetimes(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $this->expirable_sessions_service->handleSessionExpiration($request);
    }

    /**
     * Will force logout user if:
     * - expirable user session lifetime has passed,
     * - user is logged in
     *
     * @param RequestEvent $ev
     * @throws Exception
     */
    private function handleLogoutUserOnExpiredLoginSession(RequestEvent $ev): void
    {
        $request = $ev->getRequest();

        if(
                !$this->expirable_sessions_service->hasExpirableSession(ExpirableSessionsService::KEY_SESSION_USER_LOGIN_LIFETIME)
            &&  !empty($this->app->getCurrentlyLoggedInUser())
        ) {
            $message = $this->app->translator->translate('messages.general.yourSessionHasExpiredYouWereLoggedOut');

            $this->app->logoutCurrentlyLoggedInUser();
            $logout_url = $this->url_generator->generate("login");

            if( $request->isXmlHttpRequest() ){
                $ajax_response = new AjaxResponse();
                $ajax_response->setCode(Response::HTTP_TEMPORARY_REDIRECT);
                $ajax_response->setReloadPage(true);;

                $response = $ajax_response->buildJsonResponse();
            }else{
                $response = new RedirectResponse($logout_url);
            }

            $this->app->addDangerFlash($message);
            $ev->setResponse($response);
        }
    }

    /**
     * @param RequestEvent $ev
     * @param bool $is_system_lock_unlocked_before_handling_expiration
     */
    private function handleTurnLockOffOnExpiredUnlockSession(RequestEvent $ev, bool $is_system_lock_unlocked_before_handling_expiration)
    {
        $request                                           = $ev->getRequest();
        $is_system_lock_unlocked_after_handling_expiration = UserRolesSessionService::hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES);

        if(
                $is_system_lock_unlocked_before_handling_expiration
            &&  !$is_system_lock_unlocked_after_handling_expiration
        ){
            $message = $this->app->translator->translate('messages.lock.unlockExpiredReloadingPage');

            if( $request->isXmlHttpRequest() ){
                $ajax_response = new AjaxResponse();
                $ajax_response->setCode(Response::HTTP_TEMPORARY_REDIRECT);
                $ajax_response->setReloadPage(true);;

                $response = $ajax_response->buildJsonResponse();
            }else{
                $response = new RedirectResponse($request->getUri()); // the same page - just reload
            }

            $this->app->addDangerFlash($message);
            $ev->setResponse($response);
        }
    }

}