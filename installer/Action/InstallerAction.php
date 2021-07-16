<?php

namespace Installer\Action\Installer;

use Installer\Controller\Installer\InstallerController;
use Exception;
use TypeError;

include_once("../installer/Controller/InstallerController.php");

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
            $isSuccess = false;
        }

        foreach($requirementsCheckResult as $result){
            if(!$result){
                $isSuccess = false;
                break;
            }
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
            $isSuccess = false;
        }

        foreach($configurationAndPreparationResult as $result){
            if(!$result){
                $isSuccess = false;
                break;
            }
        }

        return json_encode([
            self::KEY_RESULT_CHECK_DATA => $configurationAndPreparationResult,
            self::KEY_SUCCESS           => $isSuccess,
        ]);
    }
}