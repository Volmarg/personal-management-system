<?php

namespace Installer\Services\Shell;

use Installer\Controller\Installer\InstallerController;
use Exception;
use Installer\Services\InstallerLogger;

// for compatibility with AutoInstaller
if( "cli" !== php_sapi_name() ) {
    include_once("../installer/Services/Shell/ShellAbstractService.php");
    include_once("../installer/Services/Shell/ShellPhpService.php");
    include_once("../installer/Controller/InstallerController.php");
    include_once("../installer/Services/InstallerLogger.php");
}

/**
 * Handles shell calls to php
 *
 * Class ShellPhpService
 * @package App\Services\Shell
 */
class ShellComposerService extends ShellAbstractService
{
    const EXECUTABLE_BINARY_NAME = "composer.phar";

    /**
     * Will return executable php binary name
     * @Return string
     */
    public static function getExecutableBinaryName(): string
    {
        return self::EXECUTABLE_BINARY_NAME;
    }

    /**
     * Will execute composer command - special wrapper due to necessity of using cwd etc
     * @param string $calledCommand
     * @return array - output of executed command
     * @throws Exception
     */
    protected static function executeComposerCommand(string $calledCommand): array
    {
        $executablePhpBinary = ShellPhpService::getExecutableBinaryName();
        $commandData         = self::executeShellCommandWithFullOutputLinesAndCodeAsArray("{$executablePhpBinary} " . self::getExecutableBinaryName() . " {$calledCommand}");

        $isSuccess = $commandData[self::KEY_OUTPUT_SUCCESS];
        if(!$isSuccess){
            InstallerLogger::addLogEntry("Could not install composer packages", [
                "commandData" => $commandData,
            ]);
        }

        return $commandData;
    }

    /**
     * Will install composer packages
     *
     * @return bool - true if success, false otherwise
     * @throws Exception
     */
    public static function installPackages(): bool
    {
        $callback = function() : bool {
            $output    = self::executeComposerCommand("install --ignore-platform-reqs");
            $isSuccess = $output[self::KEY_OUTPUT_SUCCESS];
            return $isSuccess;
        };

       return InstallerController::executeCallbackWithSupportOfDirectoryChange($callback);
    }

}