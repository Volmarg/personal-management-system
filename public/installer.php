<?php
/**
 * This file must remain outside of the symfony context, otherwise installation won't work as the installer
 * also handles setting the project configuration etc.
 */

include_once("../installer/Action/InstallerAction.php");

use App\Action\Installer\InstallerAction;

const KEY_GET_ENVIRONMENT_STATUS = "GET_ENVIRONMENT_STATUS";

if( !empty($_GET) ){
    header("Content-Type: application/json");

    if( array_key_exists(KEY_GET_ENVIRONMENT_STATUS, $_GET) ){
        echo InstallerAction::getRequirementsCheckResult();
        return;
    }

}else{
    // empty get - return page content
    $template = "../templates/installer/installer_base.html";
    echo file_get_contents($template);
    return;
}
