<?php

namespace App\Listeners;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Page\SettingsLockModuleController;
use App\Controller\System\LockedResourceController;
use App\Entity\System\LockedResource;
use App\Entity\User;
use App\Services\Annotation\AnnotationReaderService;
use App\Services\Exceptions\SecurityException;
use App\Services\Core\Logger;
use App\Services\Routing\UrlMatcherService;
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
     * @var Logger $securityLogger
     */
    private $securityLogger;

    /**
     * @var ExpirableSessionsService $expirableSessionsService
     */
    private $expirableSessionsService;

    /**
     * @var UrlGeneratorInterface $urlGenerator
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var UrlMatcherService $urlMatcherService
     */
    private UrlMatcherService $urlMatcherService;

    /**
     * @var AnnotationReaderService $annotationReaderService
     */
    private AnnotationReaderService $annotationReaderService;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private LockedResourceController $lockedResourceController;

    public function __construct(
        Logger                       $securityLogger,
        ExpirableSessionsService     $sessionsService,
        UrlGeneratorInterface        $urlGenerator,
        Application                  $app,
        UrlMatcherService            $urlMatcherService,
        AnnotationReaderService      $annotationReaderService,
        LockedResourceController     $lockedResourceController
    ) {
        $this->lockedResourceController     = $lockedResourceController;
        $this->annotationReaderService      = $annotationReaderService;
        $this->urlMatcherService            = $urlMatcherService;
        $this->securityLogger               = $securityLogger->getSecurityLogger();
        $this->expirableSessionsService     = $sessionsService;
        $this->urlGenerator                 = $urlGenerator;
        $this->app                          = $app;
    }

    /**
     * @param RequestEvent $ev
     * @throws SecurityException
     * @throws Exception
     *
     */
    public function onRequest(RequestEvent $ev)
    {
        $isSystemLockUnlockedBeforeHandlingExpiration = UserRolesSessionService::hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES);

        $this->handleSessionsLifetimes($ev);
        $this->handleLogoutUserOnExpiredLoginSession($ev);
        $this->handleTurnLockOffOnExpiredUnlockSession($ev, $isSystemLockUnlockedBeforeHandlingExpiration);
        $this->logRequest($ev);
        $this->blockRequestTypes($ev);
        $this->blockIp($ev);

        $this->handleAnnotations($ev);
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

        $method   = $request->getMethod();
        $getData  = json_encode($request->query->all());
        $postData = json_encode($request->request->all());
        $ip       = $request->getClientIp();
        $content  = $request->getContent();
        $headers  = json_encode($request->headers->all());
        $url      = $request->getUri();

        $this->securityLogger->info("Visited url", [
            self::LOGGER_REQUEST_URL       => $url,
            self::LOGGER_REQUEST_METHOD    => $method,
            self::LOGGER_REQUEST_GET_DATA  => $getData,
            self::LOGGER_REQUEST_POST_DATA => $postData,
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
        $requestMethod = $event->getRequest()->getMethod();
        if( !in_array($requestMethod, self::ALLOWED_REQUEST_TYPES) ){

            $response = new Response();
            $response->setContent("");

            $event->stopPropagation();
            $event->setResponse($response);

            $logMessage       = $this->app->translator->translate("logs.security.visitedPageWithUnallowedMethod");
            $exceptionMessage = $this->app->translator->translate('exceptions.security.youAreNotAllowedToSeeThis');

            $this->securityLogger->info($logMessage);
            throw new SecurityException($exceptionMessage);
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
        $restrictedIps = $this->app->configLoaders->getConfigLoaderSecurity()->getRestrictedIps();
        $request       = $event->getRequest();
        $ip            = $request->getClientIp();

        if( empty($restrictedIps) ){
            return;
        }

        if( !in_array($ip, $restrictedIps) ){
            $response = new Response();
            $response->setContent("");

            $event->stopPropagation();
            $event->setResponse($response);

            $logMessage       = $this->app->translator->translate("logs.security.visitedPageWithUnallowedIp");
            $exceptionMessage = $this->app->translator->translate('exceptions.security.youAreNotAllowedToSeeThis');

            $this->securityLogger->info($logMessage, [
                "ip" => $ip,
            ]);
            throw new SecurityException($exceptionMessage);
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
        $this->expirableSessionsService->handleSessionExpiration($request);
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
                !$this->expirableSessionsService->hasExpirableSession(ExpirableSessionsService::KEY_SESSION_USER_LOGIN_LIFETIME)
            &&  !empty($this->app->getCurrentlyLoggedInUser())
        ) {
            $message = $this->app->translator->translate('messages.general.yourSessionHasExpiredYouWereLoggedOut');

            $this->app->logoutCurrentlyLoggedInUser();
            $logoutUrl = $this->urlGenerator->generate("login");

            if( $request->isXmlHttpRequest() ){
                $ajaxResponse = new AjaxResponse();
                $ajaxResponse->setCode(Response::HTTP_TEMPORARY_REDIRECT);
                $ajaxResponse->setReloadPage(true);;

                $response = $ajaxResponse->buildJsonResponse();
            }else{
                $response = new RedirectResponse($logoutUrl);
            }

            $this->app->addDangerFlash($message);
            $ev->setResponse($response);
        }
    }

    /**
     * @param RequestEvent $ev
     * @param bool $isSystemLockUnlockedBeforeHandlingExpiration
     */
    private function handleTurnLockOffOnExpiredUnlockSession(RequestEvent $ev, bool $isSystemLockUnlockedBeforeHandlingExpiration)
    {
        $request                                     = $ev->getRequest();
        $isSystemLockUnlockedAfterHandlingExpiration = UserRolesSessionService::hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES);

        if(
                $isSystemLockUnlockedBeforeHandlingExpiration
            &&  !$isSystemLockUnlockedAfterHandlingExpiration
        ){
            $message = $this->app->translator->translate('messages.lock.unlockExpiredReloadingPage');

            if( $request->isXmlHttpRequest() ){
                $ajaxResponse = new AjaxResponse();
                $ajaxResponse->setCode(Response::HTTP_TEMPORARY_REDIRECT);
                $ajaxResponse->setReloadPage(true);

                $response = $ajaxResponse->buildJsonResponse();
            }else{
                $response = new RedirectResponse($request->getUri()); // the same page - just reload
            }

            $this->app->addDangerFlash($message);
            $ev->setResponse($response);
        }
    }

    /**
     * Will handle annotations
     *
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $ev
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function handleAnnotations(RequestEvent $ev)
    {
        $this->handleResourceLockAnnotation($ev);
    }

    /**
     * Will handle locked resource annotation
     *
     * @param RequestEvent $ev
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function handleResourceLockAnnotation(RequestEvent $ev)
    {
        $request     = $ev->getRequest();
        $classForUrl = $this->urlMatcherService->getClassForCalledUrl($request->getRequestUri());

        if( empty($classForUrl) ){
            $this->app->logger->warning("No class was found for url: " . $request->getRequestUri());
        }

        // can happen for web profiler / debug bar routes
        if( !class_exists($classForUrl) ){
            return;
        }

        /** @var ?ModuleAnnotation $annotation */
        $annotation = $this->annotationReaderService->getClassAnnotation($classForUrl, ModuleAnnotation::class);

        if( empty($annotation) ){
            return;
        }

        // check if module itself is locked
        if( !$this->lockedResourceController->isAllowedToSeeResource("", LockedResource::TYPE_ENTITY, $annotation->getName()) ){
            $this->handleNotAllowedToSeeResource($ev);
            return;
        }

        // check if all related modules are locked - if yes then the module/logic itself is not accessible
        $countOfLockedRelatedModules = 0;
        $countOfRelatedModules       = count($annotation->getRelatedModules());
        foreach($annotation->getRelatedModules() as $relatedModule){
            if( !$this->lockedResourceController->isAllowedToSeeResource("", LockedResource::TYPE_ENTITY, $relatedModule, false) ){
                $countOfLockedRelatedModules++;
            }
        }

        if(
                !empty($annotation->getRelatedModules())
            &&  $countOfLockedRelatedModules == $countOfRelatedModules
        ){
            $this->handleNotAllowedToSeeResource($ev);
            return;
        }
    }

    /**
     * Handle the case when user is not allowed to see the resource
     * @param RequestEvent $ev
     */
    private function handleNotAllowedToSeeResource(RequestEvent $ev): void
    {
        $request = $ev->getRequest();

        $targetUrl = $this->urlGenerator->generate("dashboard");
        if( $request->isXmlHttpRequest() ){
            $ajaxResponse = new AjaxResponse();
            $ajaxResponse->setRouteUrl($targetUrl);
            $response = $ajaxResponse->buildJsonResponse();
        }else{
            $response = new RedirectResponse($targetUrl);
        }

        $ev->setResponse($response);
        $ev->stopPropagation();
    }

}