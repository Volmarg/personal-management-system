<?php

namespace App\Services\Shell;

include_once("../installer/Services/ShellAbstractService.php");

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

    /**
     * Will drop database
     *
     * @return bool success or false
     */
    public static function dropDatabase(): bool
    {
        $binaryName      = self::getExecutableBinaryName();
        $executedCommand = $binaryName . " doctrine:database:drop -n --force";
        $result          = self::executeShellCommand($executedCommand);
        // todo: check if result is success - strstr + grep

        return self::EXECUTABLE_BINARY_NAME;
    }

    /**
     * Will create database
     *
     * @return bool success or false
     */
    public static function createDatabase(): bool
    {
        $binaryName      = self::getExecutableBinaryName();
        $executedCommand = $binaryName . " doctrine:database:create -n";
        $result          = self::executeShellCommand($executedCommand);
        // todo: check if result is success - strstr + grep

        return self::EXECUTABLE_BINARY_NAME;
    }

    /**
     * Will create database
     *
     * @return bool success or false
     */
    public static function executeMigrations(): bool
    {
        $binaryName      = self::getExecutableBinaryName();
        $executedCommand = $binaryName . " doctrine:migrations:migrate -n";
        $result          = self::executeShellCommand($executedCommand);
        // todo: check if result is success - strstr + grep

        return self::EXECUTABLE_BINARY_NAME;
    }

}