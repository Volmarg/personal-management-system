<?php

namespace App\Services\Shell;

/**
 * Handles shell calls to bin/console
 *
 * Class ShellBinConsoleService
 * @package App\Services\Shell
 */
class ShellBinConsoleService extends ShellAbstractService
{
    const EXECUTABLE_BINARY_NAME = "bin/console";

    public static function getExecutableBinaryName(): string
    {
        return self::EXECUTABLE_BINARY_NAME;
    }
}