<?php

namespace App\Controller\Installer;

include_once("../installer/Services/ShellMysqlService.php");
include_once("../installer/Services/ShellPhpService.php");

use App\Services\Shell\ShellMysqlService;
use App\Services\Shell\ShellPhpService;
use Exception;

/**
 * Handler of installer logic
 */
class InstallerController
{

    const PRODUCTION_REQUIREMENT_MYSQL = "Mysql";
    const PRODUCTION_REQUIREMENT_PHP   = "Php7.4";
    const PRODUCTION_REQUIREMENT_LINUX = "Linux";

    /**
     * Will check if provided database credentials are valid
     * @return bool
     */
    public static function areDatabaseCredentialsValid(): bool
    {

        return true;
    }

    /**
     * Will return array of production based environments data, with information if conditions are meet or not
     *
     * @return array
     * @throws Exception
     */
    public static function checkProductionBasedRequirements(): array
    {
        $isMysqlInstalled            = ShellMysqlService::isExecutableForServicePresent();
        $isProperPhpVersionInstalled = ShellPhpService::isProperPhpVersion();

        return [
            self::PRODUCTION_REQUIREMENT_PHP   => $isProperPhpVersionInstalled,
            self::PRODUCTION_REQUIREMENT_MYSQL => $isMysqlInstalled,
        ];
    }

}