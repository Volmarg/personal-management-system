<?php

namespace App\Controller\Modules;

use App\Controller\Core\Env;
use App\Controller\System\ModuleController;
use App\Entity\Modules\Achievements\Achievement;
use App\Entity\Modules\Contacts\MyContact;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Passwords\MyPasswords;
use App\Entity\Modules\Schedules\MySchedule;
use App\Entity\Modules\Shopping\MyShoppingPlans;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\Modules\Travels\MyTravelsIdeas;
use App\Services\Files\FilesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Todo: merge with @see ModuleController
 *
 * Class ModulesController
 * @package App\Controller\Modules
 */
class ModulesController extends AbstractController {

    const MODULE_NAME_ACHIEVEMENTS              = "Achievements";
    const MODULE_NAME_MY_SCHEDULES              = "My Schedules"; //todo: rename this const + twig
    const MODULE_NAME_CONTACTS                  = "My Contacts";
    const MODULE_NAME_FILES                     = "My Files";
    const MODULE_NAME_GOALS                     = "My Goals";
    const MODULE_NAME_TODO                      = "My Todo";
    const MODULE_NAME_IMAGES                    = "My Images";
    const MODULE_NAME_VIDEO                     = "My Video";
    const MODULE_NAME_JOB                       = "My Job";
    const MODULE_NAME_NOTES                     = "My Notes";
    const MODULE_NAME_PASSWORDS                 = "My Passwords";
    const MODULE_NAME_PAYMENTS                  = "My Payments";
    const MODULE_NAME_SHOPPING                  = "My Shopping";
    const MODULE_NAME_TRAVELS                   = "My Travels";
    const MODULE_NAME_ISSUES                    = "My Issues";
    const MENU_NODE_MODULE_NAME_REPORTS         = "My Reports";

    const MODULE_ENTITY_NOTES_CATEGORY          = "My Notes Categories"; //todo: rename this const + twig (subentity) or menu node

    const UPLOAD_MENU_RELATED_MODULES = [
        self::MODULE_NAME_FILES,
        self::MODULE_NAME_IMAGES,
        self::MODULE_NAME_VIDEO,
    ];

    const ALL_MODULES = [
        self::MODULE_NAME_ACHIEVEMENTS,
        self::MODULE_NAME_MY_SCHEDULES,
        self::MODULE_NAME_CONTACTS,
        self::MODULE_NAME_FILES,
        self::MODULE_NAME_GOALS,
        self::MODULE_NAME_TODO,
        self::MODULE_NAME_IMAGES,
        self::MODULE_NAME_VIDEO,
        self::MODULE_NAME_JOB,
        self::MODULE_NAME_NOTES,
        self::MODULE_NAME_PASSWORDS,
        self::MODULE_NAME_PAYMENTS,
        self::MODULE_NAME_SHOPPING,
        self::MODULE_NAME_TRAVELS,
        self::MODULE_NAME_ISSUES,
    ];

    const MODULE_TO_ENTITY_NAMESPACE = [
        self::MODULE_NAME_ACHIEVEMENTS              => Achievement::class,
        self::MODULE_NAME_MY_SCHEDULES              => MySchedule::class,
        self::MODULE_NAME_CONTACTS                  => MyContact::class,
        self::MODULE_NAME_FILES                     => null,
        self::MODULE_NAME_GOALS                     => MyTodo::class, // this is not a mistake as goals share basis of todoModule
        self::MODULE_NAME_TODO                      => MyTodo::class, // this is not a mistake
        self::MODULE_NAME_IMAGES                    => null ,
        self::MODULE_NAME_VIDEO                     => null ,
        self::MODULE_NAME_JOB                       => null,
        self::MODULE_NAME_NOTES                     => MyNotes::class,
        self::MODULE_NAME_PASSWORDS                 => MyPasswords::class,
        self::MODULE_NAME_PAYMENTS                  => null,
        self::MODULE_NAME_SHOPPING                  => MyShoppingPlans::class,
        self::MODULE_NAME_TRAVELS                   => MyTravelsIdeas::class,
        self::MODULE_NAME_ISSUES                    => MyIssue::class,
    ];

    /**
     * Will map module name to entity namespace
     *
     * @param string $moduleName
     * @return string|null
     */
    public static function getEntityNamespaceForModuleName(string $moduleName): ?string
    {
        if( !self::isModuleDefined($moduleName) ){
            return null;
        }

        return self::MODULE_TO_ENTITY_NAMESPACE[$moduleName];
    }

    /**
     * Will check weather given module name exists at all in the system
     *
     * @param string $moduleName
     * @return bool
     */
    public static function isModuleDefined(string $moduleName): bool
    {
        return in_array($moduleName, self::ALL_MODULES);
    }

    /**
     * Returns the file based module name for full file path
     *
     * @param string $fileFullPath
     * @return string|null
     */
    public static function getUploadModuleNameForFileFullPath(string $fileFullPath): ?string
    {
        $trimmedFileFullPath = FilesHandler::trimFirstAndLastSlash($fileFullPath);

        switch(true)
        {
            case preg_match("#^" . Env::getImagesUploadDir() . "#", $trimmedFileFullPath):
            {
                return self::MODULE_NAME_IMAGES;
            }

            case preg_match("#^" . Env::getFilesUploadDir() . "#", $trimmedFileFullPath):
            {
                return self::MODULE_NAME_FILES;
            }

            case preg_match("#^" . Env::getVideoUploadDir() . "#", $trimmedFileFullPath):
            {
                return self::MODULE_NAME_VIDEO;
            }

            default:
            {
                return null;
            }
        }

    }
}
