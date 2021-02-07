<?php


namespace App\Action\System;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Entity\System\LockedResource;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LockedResourceAction extends AbstractController {

    const KEY_RECORD = "record";
    const KEY_TYPE   = "type";
    const KEY_TARGET = "target";

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers  $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/api/lock-resource/toggle", name="api_lock_resource_toggle", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * 
     * @throws Exception
     */
    public function toggleLock(Request $request): JsonResponse
    {

        if( !$request->request->has(self::KEY_RECORD) ){
            $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . self::KEY_RECORD;
            throw new \Exception($message);
        }

        if( !$request->request->has(self::KEY_TYPE) ){
            $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . self::KEY_TYPE;
            throw new \Exception($message);
        }

        if( !$request->request->has(self::KEY_TARGET) ){
            $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . self::KEY_TARGET;
            throw new \Exception($message);
        }

        $record = $request->request->get(self::KEY_RECORD);
        $type   = $request->request->get(self::KEY_TYPE);
        $target = $request->request->get(self::KEY_TARGET);

        //first check if the record exists if not then we create new, otherwise we have removal
        $lockedResource = $this->controllers->getLockedResourceController()->findOneEntity($record, $type, $target);
        $code           = 200;

        try{

            if( !empty($lockedResource) ){
                $this->controllers->getLockedResourceController()->remove($lockedResource);;
                $message = $this->app->translator->translate("messages.lock.resourceHasBeenUnlocked");
            }else{
                $lockedResource = new LockedResource();
                $lockedResource->setRecord($record);
                $lockedResource->setType($type);
                $lockedResource->setTarget($target);

                $this->controllers->getLockedResourceController()->add($lockedResource);
                $message = $this->app->translator->translate("messages.lock.resourceHasBeenLocked");
            }

        } catch(Exception $e){
            $code    = 500;
            $message = $this->app->translator->translate("messages.lock.couldNotLockResource");
            $this->app->logger->critical($message, [
                "exceptionMessage" => $e->getMessage(),
                "exceptionCode"    => $e->getCode(),
            ]);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
    }


}