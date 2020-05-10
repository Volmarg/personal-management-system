<?php


namespace App\Action\System;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
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
    private $app;


    public function __construct(Application $app) {
        $this->app = $app;
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