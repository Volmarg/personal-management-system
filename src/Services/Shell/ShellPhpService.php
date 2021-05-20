<?php

namespace App\Services\Shell;

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

    const EXECUTABLE_BINARY_NAME = "bin/console";

    public static function getExecutableBinaryName(): string
    {
        $executableBinaryName = self::decideExecutablePhpBinary();
        return $executableBinaryName;
    }

    /**
     * Will define the php executable to be called, as depending if apache or fpm is used the executable can vary
     */
    private static function decideExecutablePhpBinary(): string
    {
        $php74 = trim(shell_exec("which " . self::PHP_EXECUTABLE_7_4));
        if( !empty($php74) ){
            return self::PHP_EXECUTABLE_7_4;
        }else{
            return self::PHP_EXECUTABLE_DEFAULT;
        }
    }

}