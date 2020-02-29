<?php

namespace App\Controller\System;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Entity\System\LockedResource;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LockedResourceController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/api/lock-resource/toggle/{record}/{type}/{target}", name="api_lock_resource_toggle", methods="GET")
     * @param Request $request
     * @param string|null $record
     * @param string|null $type
     * @param string|null $target
     * @return JsonResponse
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function toggleLock(Request $request, ?string $record = null, ?string $type = null, ?string $target = null): JsonResponse
    {
        //first check if the record exists if not then we create new, otherwise we have removal
        $locked_resource = $this->app->repositories->lockedResourceRepository->findOne($record, $type, $target);
        $code            = 200;

        try{

            if( !empty($locked_resource) ){
                $this->app->repositories->lockedResourceRepository->remove($locked_resource);
                $message = $this->app->translator->translate("messages.lock.resourceHasBeenUnlocked");
            }else{
                $locked_resource = new LockedResource();
                $locked_resource->setRecord($record);
                $locked_resource->setType($type);
                $locked_resource->setTarget($target);

                $this->app->repositories->lockedResourceRepository->add($locked_resource);
                $message = $this->app->translator->translate("messages.lock.resourceHasBeenLocked");
            }

        } catch(Exception $e){
            $code    = 500;
            $message = $this->app->translator->translate("messages.lock.couldNotLockResource");
        }

        return AjaxResponse::buildResponseForAjaxCall($code, $message);
    }
}
