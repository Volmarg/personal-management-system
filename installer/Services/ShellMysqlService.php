<?php

namespace App\Services\Shell;

include_once("../installer/Services/ShellAbstractService.php");

use Exception;

/**
 * Handles shell calls to php
 *
 * Class ShellPhpService
 * @package App\Services\Shell
 */
class ShellMysqlService extends ShellAbstractService
{
    const EXECUTABLE_BINARY_NAME = "mysql";

    /**
     * Will return executable php binary name
     * @throws Exception
     */
    public static function getExecutableBinaryName(): string
    {
        return self::EXECUTABLE_BINARY_NAME;
    }

}