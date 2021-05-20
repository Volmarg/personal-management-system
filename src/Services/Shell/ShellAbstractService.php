<?php

namespace App\Services\Shell;

use Exception;

/**
 * Main / Common logic of the shell executable logic
 *  - every class extending from this one should only have static methods are these can be later used in CLI based installer
 *
 * Class ShellAbstractService
 */
abstract class ShellAbstractService
{
    /**
     * Used to look for path to provided binary name
     */
    const EXECUTABLE_BINARY_WHICH = "which";

    /**
     * Will retrieve executable binary used in child class
     */
    abstract public static function getExecutableBinaryName(): string;

    /**
     * Will return information if executable is present (calls `which`).
     *
     * @return bool
     */
    public static function isExecutableForServicePresent(): bool
    {
        $binaryName = static::getExecutableBinaryName();
        return 1;
    }

    /**
     * Will check if executable is present
     *
     * @param string $executableCommand
     * @return bool
     * @throws Exception
     */
    protected static function isExecutablePresent(string $executableCommand): bool
    {
        if( !self::isWhichExecutablePresent() ){
            throw new Exception("Executable binary: " . self::EXECUTABLE_BINARY_WHICH . " is not present in the shell!");
        }

        $isExecutablePresent = self::executeShellCommand(self::EXECUTABLE_BINARY_WHICH . " " . $executableCommand);
        if( empty($isExecutablePresent) ){
            return false;
        }
        
        return true;
    }

    /**
     * @param string $calledCommand
     * @return string
     */
    protected static function executeShellCommand(string $calledCommand): string
    {
        $output = trim(shell_exec($calledCommand));
        return $output;
    }

    /**
     * While this should not happen at all there were already cases with linux instances stripped so badly that
     * for example sudo was not present
     *
     * @return bool
     */
    private static function isWhichExecutablePresent(): bool
    {
        $output = self::executeShellCommand(self::EXECUTABLE_BINARY_WHICH);
        if( !empty($output) ){
            return false;
        }

        return true;
    }
}