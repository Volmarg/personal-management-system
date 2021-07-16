<?php

namespace Installer\Services\Shell;

// for compatibility with AutoInstaller
if( "cli" !== php_sapi_name() ) {
    include_once("../installer/Services/Shell/ShellAbstractService.php");
    include_once("../installer/Services/InstallerLogger.php");
}

use Exception;
use Installer\Services\InstallerLogger;

/**
 * Handles shell calls to php
 *
 * Class ShellPhpService
 * @package App\Services\Shell
 */
class ShellPhpService extends ShellAbstractService
{
    const PHP_EXECUTABLE_DEFAULT = "php";
    const PHP_EXECUTABLE_7_4     = "php7.4";

    const REQUIRED_PHP_VERSION = "7.4";

    const ALL_POSSIBLE_PHP_EXECUTABLES = [
        self::PHP_EXECUTABLE_7_4,
        self::PHP_EXECUTABLE_DEFAULT,
    ];

    /**
     * Will return executable php binary name
     * @throws Exception
     */
    public static function getExecutableBinaryName(): string
    {
        $executableBinaryName = self::decideExecutablePhpBinary();
        return $executableBinaryName;
    }

    /**
     * Will return information if php version is proper
     *
     * @return bool
     * @throws Exception
     */
    public static function isProperPhpVersion(): bool {
        $binaryName = self::getExecutableBinaryName();

        $result = trim(shell_exec("$binaryName -v | grep 'PHP " . self::REQUIRED_PHP_VERSION . "'"));
        return !empty($result);
    }

    /**
     * Will define the php executable to be called, as depending if apache or fpm is used the executable can vary
     *
     * @throws Exception
     * @return string
     */
    private static function decideExecutablePhpBinary(): string
    {
        foreach(self::ALL_POSSIBLE_PHP_EXECUTABLES as $executable){
            if( self::isExecutablePresent($executable) ){
                return $executable;
            }
        }

        $message = "Could not find php executable!";
        InstallerLogger::addLogEntry($message);
        throw new Exception($message);
    }

}