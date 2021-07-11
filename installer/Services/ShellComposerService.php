<?php

namespace App\Services\Shell;

include_once("../installer/Services/ShellAbstractService.php");

/**
 * Handles shell calls to php
 *
 * Class ShellPhpService
 * @package App\Services\Shell
 */
class ShellComposerService extends ShellAbstractService
{
    const EXECUTABLE_BINARY_NAME = "composer";

    /**
     * Will return executable php binary name
     * @Return string
     */
    public static function getExecutableBinaryName(): string
    {
        return self::EXECUTABLE_BINARY_NAME;
    }

}