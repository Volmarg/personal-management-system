<?php

namespace App\Controller\System;

use App\Controller\Core\Application;
use App\Entity\System\LockedResource;
use App\Entity\User;
use App\Services\Session\UserRolesSessionService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LockedResourceController extends AbstractController {

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
                    $replace = DIRECTORY_SEPARATOR . preg_quote($matches[2]);
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
        return !$this->user_roles_session_service->hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES);
    }

    /**
     * @param string $record
     * @param string $type
     * @param string $target
     * @param bool $show_flash_message
     * @return bool
     *
     * @throws Exception
     */
    public function isAllowedToSeeResource(string $record, string $type, string $target, bool $show_flash_message = true): bool
    {
        $is_resource_locked = $this->isResourceLocked($record, $type, $target);
        $is_system_locked   = $this->isSystemLocked();

        if(
                ( $is_resource_locked && !$is_system_locked )
            ||  ( !$is_resource_locked )
        ){
            return true;
        }

        if($show_flash_message){
            $message = $this->app->translator->translate("responses.lockResource.youAreNotAllowedToSeeThisResource");
            $this->app->addDangerFlash($message);
        }
        return false;
    }

}
