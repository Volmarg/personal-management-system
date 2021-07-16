<?php
/**
 * This file must remain outside of the symfony context, otherwise installation won't work as the installer
 * also handles setting the project configuration etc.
 */

include_once("../installer/Action/InstallerAction.php");
include_once("../installer/Controller/InstallerController.php");
include_once("../installer/Services/EnvBuilder.php");

use Installer\Action\Installer\InstallerAction;
use Installer\Controller\Installer\InstallerController;

$envFilePath = "../.env";
if( InstallerController::isInstalled($envFilePath) ){
    header("Location: /login");
    return;
}

if( InstallerController::wasAlreadyInstalled($envFilePath) ){
    InstallerController::setEnvKeyAppIsInstalled();
    header("Location: /login");
    return;
}

const KEY_GET_ENVIRONMENT_STATUS       = "GET_ENVIRONMENT_STATUS";
const KEY_STEP_CONFIGURATION_EXECUTION = "STEP_CONFIGURATION_EXECUTION";
const KEY_GET_LOG_FILE_CONTENT         = "GET_LOG_FILE_CONTENT";

if( !empty($_GET) ){
    header("Content-Type: application/json");

    if( array_key_exists(KEY_GET_ENVIRONMENT_STATUS, $_GET) ){
        echo InstallerAction::getRequirementsCheckResult();
        return;
    }else if( array_key_exists(KEY_STEP_CONFIGURATION_EXECUTION, $_GET) ){
        echo InstallerAction::configureAndPrepareSystem();
        return;
    }else if( array_key_exists(KEY_GET_LOG_FILE_CONTENT, $_GET) ){
        echo InstallerAction::getLogFileContent();
        return;
    }

}else{
    // empty get - return page content
    $template = "../templates/installer/installer_base.html";
    echo file_get_contents($template);
    return;
}
