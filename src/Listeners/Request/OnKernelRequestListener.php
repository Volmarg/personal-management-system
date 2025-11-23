<?php

namespace App\Listeners\Request;

use App\Annotation\System\ModuleAnnotation;
use App\Entity\System\LockedResource;
use App\Response\Base\BaseResponse;
use App\Response\Security\LockedResourceDeniedResponse;
use App\Services\Attribute\AttributeReaderService;
use App\Services\ConfigLoaders\ConfigLoaderSecurity;
use App\Services\Exceptions\SecurityException;
use App\Services\Routing\UrlMatcherService;
use App\Services\System\LockedResourceService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Security layer for logging every single page call
 *  - first of all for security reason
 *  - second the request data might end up in log so if something fails during insert it might be recovered this way
 */
class OnKernelRequestListener implements EventSubscriberInterface {

    /**
     * @var UrlMatcherService $urlMatcherService
     */
    private UrlMatcherService $urlMatcherService;

    /**
     * @var LockedResourceService $lockedResourceService
     */
    private LockedResourceService $lockedResourceService;

    public function __construct(
        UrlMatcherService                       $urlMatcherService,
        LockedResourceService                   $lockedResourceService,
        private readonly ConfigLoaderSecurity   $configLoaderSecurity,
        private readonly LoggerInterface        $requestLogger,
        private readonly LoggerInterface        $logger,
        private readonly LoggerInterface        $securityLogger,
        private readonly AttributeReaderService $attributeReaderService,
    ) {
        $this->lockedResourceService   = $lockedResourceService;
        $this->urlMatcherService            = $urlMatcherService;
    }

    /**
     * @param RequestEvent $ev
     *
     * @throws SecurityException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function onRequest(RequestEvent $ev): void
    {
        $this->logRequest($ev);
        $this->blockIp($ev);
        $this->handleAnnotations($ev);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest'],
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

        $this->requestLogger->info("Visited url", [
            "requestUrl"      => $url,
            "requestMethod"   => $method,
            "requestGetData"  => $getData,
            "requestPostData" => $postData,
            "requestIp"       => $ip,
            "requestContent"  => $content,
            "requestHeaders"  => $headers,
        ]);
    }

    /**
     * @param RequestEvent $event
     * @throws SecurityException
     * @throws Exception
     *
     */
    private function blockIp(RequestEvent $event): void
    {
        $restrictedIps = $this->configLoaderSecurity->getRestrictedIps();
        $request       = $event->getRequest();
        $ip            = $request->getClientIp();

        if (empty($restrictedIps)) {
            return;
        }

        if (!in_array($ip, $restrictedIps)) {
            $msg      = "Not allowed to access from this ip: {$ip}";
            $response = BaseResponse::buildAccessDeniedResponse($msg);

            $event->stopPropagation();
            $event->setResponse($response->toJsonResponse());

            $this->securityLogger->info($msg, [
                "ip" => $ip,
            ]);
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
        if (empty($classForUrl)) {
            $this->logger->warning("No class was found for url: " . $request->getRequestUri());
        }

        // can happen for web profiler / debug bar routes
        if (!class_exists($classForUrl)) {
            return;
        }

        $attribute = $this->attributeReaderService->getClassAttribute($classForUrl, ModuleAnnotation::class);
        if (empty($attribute)) {
            return;
        }

        // check if module itself is locked
        $attributeArgs = $attribute->getArguments();
        $values = $attributeArgs['values'] ?? [];
        if (empty($values)) {
            $this->logger->critical("Key `values` does not exist for: " . ModuleAnnotation::class);
            return;
        }

        $moduleName = $values[ModuleAnnotation::ATTRIBUTE_KEY_NAME] ?? null;
        $relatedModules = $values[ModuleAnnotation::ATTRIBUTE_KEY_RELATED_MODULES] ?? [];

        if (empty($moduleName)) {
            throw new Exception(ModuleAnnotation::ATTRIBUTE_KEY_NAME . " was not set for: " . ModuleAnnotation::class);
        }

        if( !$this->lockedResourceService->isAllowedToSeeResource("", LockedResource::TYPE_ENTITY, $moduleName) ){
            $this->handleNotAllowedToSeeResource($ev);
            return;
        }

        // check if all related modules are locked - if yes then the module/logic itself is not accessible
        $countOfLockedRelatedModules = 0;
        $countOfRelatedModules       = count($relatedModules);
        foreach($relatedModules as $relatedModule){
            if (!$this->lockedResourceService->isAllowedToSeeResource("", LockedResource::TYPE_ENTITY, $relatedModule, false)){
                $countOfLockedRelatedModules++;
            }
        }

        if(
                !empty($relatedModules)
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
        $response = LockedResourceDeniedResponse::build()->toJsonResponse();
        $ev->setResponse($response);
        $ev->stopPropagation();
    }

}