<?php

namespace App\Controller\Modules;

use App\Controller\Core\Env;
use App\Services\Files\FilesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
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
    public const MODULE_NAME_SYSTEM = "System";
    public const MODULE_NAME_STORAGE = "Storage";

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
