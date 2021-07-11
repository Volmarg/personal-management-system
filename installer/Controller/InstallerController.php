<?php

namespace App\Controller\Installer;

include_once("../installer/Services/ShellMysqlService.php");
include_once("../installer/Services/ShellPhpService.php");
include_once("../installer/Services/ShellComposerService.php");

use App\Services\Shell\ShellComposerService;
use App\Services\Shell\ShellMysqlService;
use App\Services\Shell\ShellPhpService;
use Exception;

/**
 * Handler of installer logic
 */
class InstallerController
{

    const PRODUCTION_REQUIREMENT_MYSQL               = "Mysql executable exists";
    const PRODUCTION_REQUIREMENT_MYSQL_ACCESS_VALID  = "Mysql access valid";
    const PRODUCTION_REQUIREMENT_MYSQL_MODE_DISABLED = "Mysql mode has " . ShellMysqlService::MYSQL_MODE_ONLY_FULL_GROUP_BY . " disabled";
    const PRODUCTION_REQUIREMENT_PHP                 = "Php7.4 installed";
    const PRODUCTION_REQUIREMENT_COMPOSER            = "Composer global executable exists";
    const PRODUCTION_REQUIREMENT_LINUX               = "Linux OS";

    const PARAM_DB_LOGIN    = "databaseLogin";
    const PARAM_DB_NAME     = "databaseName";
    const PARAM_DB_PASSWORD = "databasePassword";
    const PARAM_DB_PORT     = "databasePort";
    const PARAM_DB_HOST     = "databaseHost";

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
        $isComposerInstalled         = ShellComposerService::isExecutableForServicePresent();


        $returnedData = [
            self::PRODUCTION_REQUIREMENT_PHP      => $isProperPhpVersionInstalled,
            self::PRODUCTION_REQUIREMENT_MYSQL    => $isMysqlInstalled,
            self::PRODUCTION_REQUIREMENT_COMPOSER => $isComposerInstalled,
        ];

        if($isMysqlInstalled){
            $requestJson = file_get_contents("php://input");
            $requestData = json_decode($requestJson, true);

            $databaseLogin    = $requestData[self::PARAM_DB_LOGIN];
            $databasePassword = $requestData[self::PARAM_DB_PASSWORD];
            $databasePort     = $requestData[self::PARAM_DB_PORT];
            $databaseHost     = $requestData[self::PARAM_DB_HOST];

            $isDbPasswordValid                                             = ShellMysqlService::isDbAccessValid($databaseLogin, $databaseHost, $databasePort, $databasePassword);
            $returnedData[self::PRODUCTION_REQUIREMENT_MYSQL_ACCESS_VALID] = $isDbPasswordValid;

            if($isDbPasswordValid){
                $isOnlyFullGroupByMysqlModeSet                                  = ShellMysqlService::isOnlyFullGroupByMysqlModeDisabled($databaseLogin, $databaseHost, $databasePort, $databasePassword);
                $returnedData[self::PRODUCTION_REQUIREMENT_MYSQL_MODE_DISABLED] = $isOnlyFullGroupByMysqlModeSet;
            }

        }

        return $returnedData;
    }

    /**
     * Will execute configuration / system preparation logic
     *
     * @return array
     */
    public static function configureAndPrepareSystem(): array
    {

        return [];
    }

    public static function buildEnvFile(){

    }

}