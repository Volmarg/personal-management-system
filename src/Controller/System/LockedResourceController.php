<?php

namespace App\Controller\System;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Entity\System\LockedResource;
use App\Entity\User;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use App\Services\Session\UserRolesSessionService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LockedResourceController extends AbstractController {

    const KEY_RECORD = "record";
    const KEY_TYPE   = "type";
    const KEY_TARGET = "target";

    /**
     * @var Application
     */
    private $app;

    /**
     * @var UserRolesSessionService $user_roles_session_service
     */
    private $user_roles_session_service;

    public function __construct(Application $app, UserRolesSessionService $user_roles_session_service) {
        $this->user_roles_session_service = $user_roles_session_service;
        $this->app                        = $app;
    }

    /**
     * // todo: change implementation in JS to POST
     * @Route("/api/lock-resource/toggle", name="api_lock_resource_toggle", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionDuplicatedTranslationKey
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

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @return bool
     * @throws Exception
     */
    public function isResourceLocked(string $record, string $type, string $target): bool
    {
        switch($type){
            case LockedResource::TYPE_ENTITY:
                $locked_resource = $this->app->repositories->lockedResourceRepository->findOne($record, $type, $target);

                if( empty($locked_resource) ){
                    return false;
                }
                return true;
            break;
            // in case of directory we need to check every parent directory for lock
            // if any parent is locked then we also lock given directory
            case LockedResource::TYPE_DIRECTORY:

                $pattern = "#(.*)[\/]{1}(.*)#";
                while( preg_match($pattern, $record, $matches) ){ # walk over the path and build parent path

                    $locked_resource = $this->app->repositories->lockedResourceRepository->findOne($record, $type, $target);
                    if( !empty($locked_resource) ){
                        return true;
                    }

                    if( !array_key_exists(2, $matches) ){
                        return false;
                    }
                    $replace = DIRECTORY_SEPARATOR . $matches[2];
                    $record  = preg_replace("#{$replace}#", "", $record);

                }

                return false;

                break;
            default:
                throw new Exception("This locked resource type is not supported");
        }

    }

    /**
     * @return bool
     */
    public function isSystemLocked(): bool
    {
        return $this->user_roles_session_service->hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES);
    }

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @return bool
     */
    public function isResourceVisible(string $record, string $type, string $target): bool
    {
        $is_resource_locked      = $this->isResourceLocked($record, $type, $target);
        $can_see_locked_resource = $this->user_roles_session_service->hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES);

        if(
            !$is_resource_locked
            ||  (
                $is_resource_locked && $can_see_locked_resource
            )
        ){
            return true;
        }

        return false;
    }


}
