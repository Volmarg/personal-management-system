<?php

namespace App\Listeners\Request\Kernel;

use App\Attribute\ModuleAttribute;
use App\Entity\System\LockedResource;
use App\Response\Security\LockedResourceDeniedResponse;
use App\Services\Attribute\AttributeReaderService;
use App\Services\Routing\UrlMatcherService;
use App\Services\System\LockedResourceService;
use Exception;
use LogicException;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest', priority: 1)]
class KernelRequestResourceLock
{
    public function __construct(
        private readonly UrlMatcherService      $urlMatcherService,
        private readonly LockedResourceService  $lockedResourceService,
        private readonly LoggerInterface        $logger,
        private readonly AttributeReaderService $attributeReaderService,
    ) {
    }

    /**
     * @param RequestEvent $ev
     *
     * @throws ReflectionException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function onKernelRequest(RequestEvent $ev): void
    {
        $className = $this->getClassNameForCalledUri($ev->getRequest());
        $attribute = $this->attributeReaderService->getClassAttribute($className, ModuleAttribute::class);
        if (empty($attribute)) {
            return;
        }

        $attributeArgs = $attribute->getArguments();
        $values        = $attributeArgs['values'] ?? [];
        if (empty($values)) {
            $this->logger->critical("Key `values` does not exist for: " . ModuleAttribute::class);
            return;
        }

        $moduleName     = $values[ModuleAttribute::ATTRIBUTE_KEY_NAME] ?? null;
        $relatedModules = $values[ModuleAttribute::ATTRIBUTE_KEY_RELATED_MODULES] ?? [];

        if (empty($moduleName)) {
            throw new Exception(ModuleAttribute::ATTRIBUTE_KEY_NAME . " was not set for: " . ModuleAttribute::class);
        }

        if (!$this->canAccessModule($moduleName) && !$this->canAccessMethod($ev)) {
            $this->handleNotAllowedToSeeResource($ev);
            return;
        }

        // check if all related modules are locked - if yes then the module/logic itself is not accessible
        $countOfRelatedModules       = count($relatedModules);
        $countOfLockedRelatedModules = $this->countRelatedLockedModules($relatedModules);

        if (!empty($relatedModules) && $countOfLockedRelatedModules == $countOfRelatedModules) {
            $this->handleNotAllowedToSeeResource($ev);
        }
    }

    /**
     * Handle the case when user is not allowed to see the resource
     *
     * @param RequestEvent $ev
     */
    private function handleNotAllowedToSeeResource(RequestEvent $ev): void
    {
        $response = LockedResourceDeniedResponse::build()->toJsonResponse();
        $ev->setResponse($response);
        $ev->stopPropagation();
    }

    /**
     * @param string $calledUri
     *
     * @return bool
     * @throws ReflectionException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function isCalledMethodLocked(string $calledUri): bool
    {
        $attributes = $this->attributeReaderService->getUriAttribute($calledUri, ModuleAttribute::class);
        foreach ($attributes as $attribute) {
            $args       = $attribute->getArguments();
            $values     = $args['values'] ?? [];
            $moduleName = $values[ModuleAttribute::ATTRIBUTE_KEY_NAME] ?? null;
            if (empty($moduleName)) {
                throw new LogicException("`{$calledUri}`: module name was not set for: " . ModuleAttribute::class);
            }

            if ($this->lockedResourceService->isAllowedToSeeResource("", LockedResource::TYPE_MODULE, $moduleName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Request $request
     *
     * @return string
     * @throws Exception
     */
    private function getClassNameForCalledUri(Request $request): string
    {
        $classAndMethod      = $this->urlMatcherService->getClassAndMethodForCalledUrl($request->getRequestUri());
        $classAndMethodParts = explode("::", $classAndMethod);
        $className           = $classAndMethodParts[0];

        // can happen for web profiler / debug bar routes
        if (empty($className)) {
            throw new LogicException("Could not find class name for called uri {$request->getRequestUri()}");
        }

        return $className;
    }

    /**
     * @param RequestEvent $ev
     *
     * @return bool
     * @throws ReflectionException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function canAccessMethod(RequestEvent $ev): bool
    {
        $canAccessMethod = false;
        if ($this->attributeReaderService->hasUriAttribute($ev->getRequest()->getRequestUri(), ModuleAttribute::class)) {
            $canAccessMethod = !$this->isCalledMethodLocked($ev->getRequest()->getRequestUri());
        }

        return $canAccessMethod;
    }

    /**
     * @param mixed $moduleName
     *
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function canAccessModule(string $moduleName): bool
    {
        return $this->lockedResourceService->isAllowedToSeeResource("", LockedResource::TYPE_ENTITY, $moduleName);
    }

    /**
     * @param mixed $relatedModules
     *
     * @return int
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function countRelatedLockedModules(mixed $relatedModules): int
    {
        $countOfLockedRelatedModules = 0;
        foreach ($relatedModules as $relatedModule) {
            if (!$this->lockedResourceService->isAllowedToSeeResource("", LockedResource::TYPE_ENTITY, $relatedModule, false)) {
                $countOfLockedRelatedModules++;
            }
        }

        return $countOfLockedRelatedModules;
    }
}