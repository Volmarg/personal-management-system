<?php

namespace App\Services\Module;

use App\Services\Files\FilesHandler;
use App\Services\System\EnvReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModulesService extends AbstractController {

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
    const MODULE_NAME_ISSUES  = "My Issues";
    const MODULE_NAME_REPORTS = "My Reports";

    // this also defines listing order, keep in mind that front uses these strings for mapping `name backend => name frontend`
    public const ALL_MODULES = [
        self::MODULE_NAME_GOALS,
        self::MODULE_NAME_TODO,
        self::MODULE_NAME_NOTES,
        self::MODULE_NAME_CONTACTS,
        self::MODULE_NAME_PASSWORDS,
        self::MODULE_NAME_ACHIEVEMENTS,
        self::MODULE_NAME_MY_SCHEDULES,
        self::MODULE_NAME_ISSUES,
        self::MODULE_NAME_TRAVELS,
        self::MODULE_NAME_PAYMENTS,
        self::MODULE_NAME_SHOPPING,
        self::MODULE_NAME_JOB,
        self::MODULE_NAME_REPORTS,
        self::MODULE_NAME_FILES,
        self::MODULE_NAME_IMAGES,
        self::MODULE_NAME_VIDEO,
    ];

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
            case preg_match("#^" . EnvReader::getImagesUploadDir() . "#", $trimmedFileFullPath):
            {
                return self::MODULE_NAME_IMAGES;
            }

            case preg_match("#^" . EnvReader::getFilesUploadDir() . "#", $trimmedFileFullPath):
            {
                return self::MODULE_NAME_FILES;
            }

            case preg_match("#^" . EnvReader::getVideoUploadDir() . "#", $trimmedFileFullPath):
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
