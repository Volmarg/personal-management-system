<?php
/**
 * This file must remain outside of the symfony context, otherwise installation won't work as the installer
 * also handles setting the project configuration etc.
 */

// also update autoinstaller code

include_once("../installer/Action/InstallerAction.php");

// already installed
if(
        file_exists("../.env")
    ||  file_exists("../vendor")
){
    header("Location: /login");
    return;
}

use App\Action\Installer\InstallerAction;

const KEY_GET_ENVIRONMENT_STATUS       = "GET_ENVIRONMENT_STATUS";
const KEY_STEP_CONFIGURATION_EXECUTION = "STEP_CONFIGURATION_EXECUTION";

if( !empty($_GET) ){
    header("Content-Type: application/json");

    if( array_key_exists(KEY_GET_ENVIRONMENT_STATUS, $_GET) ){
        echo InstallerAction::getRequirementsCheckResult();
        return;
    }else if( array_key_exists(KEY_STEP_CONFIGURATION_EXECUTION, $_GET) ){
        echo InstallerAction::getRequirementsCheckResult();
        return;
    }


}else{
    // empty get - return page content
    $template = "../templates/installer/installer_base.html";
    echo file_get_contents($template);
    return;
}
