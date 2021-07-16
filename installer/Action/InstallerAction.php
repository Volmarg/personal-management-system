<?php

namespace Installer\Action\Installer;

use Installer\Controller\Installer\InstallerController;
use Exception;
use Installer\Services\InstallerLogger;
use Installer\Services\Shell\EnvBuilder;
use TypeError;

include_once("../installer/Controller/InstallerController.php");
include_once("../installer/Services/EnvBuilder.php");
include_once("../installer/Services/InstallerLogger.php");

/**
 * Actions for handling the GUI based installation process
 * - not supporting ajax calls in this step, this is not needed,
 * - only production mode is supported in this case,
 *
 * Class InstallerAction
 */
class InstallerAction
{
    const KEY_SUCCESS           = "success";
    const KEY_RESULT_CHECK_DATA = "resultCheckData";
    const KEY_LOG_FILE_CONTENT  = "logFileContent";

    /**
     * Will return requirements check result
     *
     * @return string
     */
    public static function getRequirementsCheckResult(): string
    {
        $isSuccess               = true;
        $requirementsCheckResult = [];

        try{
            $requirementsCheckResult = InstallerController::checkProductionBasedRequirements();
        }catch(Exception | TypeError $e){
            InstallerLogger::addLogEntry("Exception was throw while checking requirements", [
                "message" => $e->getMessage(),
            ]);
            $isSuccess = false;
        }

        foreach($requirementsCheckResult as $result){
            if(!$result){
                $isSuccess = false;
                break;
            }
        }

        if( empty($requirementsCheckResult) ){
            $isSuccess = false;
        }

        return json_encode([
            self::KEY_RESULT_CHECK_DATA => $requirementsCheckResult,
            self::KEY_SUCCESS           => $isSuccess,
        ]);
    }

    /**
     * Will execute configuration / system preparation logic
     *
     * @return string
     */
    public static function configureAndPrepareSystem(): string
    {
        $isSuccess                         = true;
        $configurationAndPreparationResult = [];

        try{
            $configurationAndPreparationResult = InstallerController::configureAndPrepareSystem();
        }catch(Exception | TypeError $e){
            InstallerLogger::addLogEntry("Exception was throw while configuring and preparing system", [
                "message" => $e->getMessage(),
            ]);

            $isSuccess = false;
        }

        foreach($configurationAndPreparationResult as $result){
            if(!$result){
                $isSuccess = false;
                break;
            }
        }

        if( empty($configurationAndPreparationResult) ){
            $isSuccess = false;
        }

        if(!$isSuccess){
            EnvBuilder::removeEnvFile();
        }

        return json_encode([
            self::KEY_RESULT_CHECK_DATA => $configurationAndPreparationResult,
            self::KEY_SUCCESS           => $isSuccess,
        ]);
    }

    /**
     * Will return log file content
     *
     * @return string
     */
    public static function getLogFileContent(): string {
        $logFilePath    = InstallerLogger::findLogFilePath();
        $logFileContent = file_get_contents($logFilePath);
        $formattedLog   = nl2br($logFileContent);

        return json_encode([
            self::KEY_LOG_FILE_CONTENT => $formattedLog,
        ]);
    }
}