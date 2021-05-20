<?php


namespace App\Services\Shell;

/**
 * Handles shell calls to `mysqli`
 *
 * Class ShellDatabaseService
 * @package App\Services\Shell
 */
class ShellDatabaseService extends ShellAbstractService
{
    const EXECUTABLE_BINARY_NAME = "mysqli";

    public static function getExecutableBinaryName(): string
    {
        return self::EXECUTABLE_BINARY_NAME;
    }
}