<?php

namespace Installer\Services\Shell;

use Exception;
use Installer\Services\InstallerLogger;

if( "cli" !== php_sapi_name() )
{
    include_once("../installer/Services/InstallerLogger.php");
}
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

    const KEY_EXECUTED_COMMAND    = "command";
    const KEY_OUTPUT_RESULT       = "output";
    const KEY_OUTPUT_CODE         = "code";
    const KEY_OUTPUT_SUCCESS      = "success";

    const EXEC_CODE_SUCCESS = 0;

    /**
     * Will retrieve executable binary used in child class
     */
    abstract public static function getExecutableBinaryName(): string;

    /**
     * Will return information if executable is present (calls `which`).
     *
     * @return bool
     * @throws Exception
     */
    public static function isExecutableForServicePresent(): bool
    {
        $binaryName = static::getExecutableBinaryName();
        return self::isExecutablePresent($binaryName);
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
            $message = "Executable binary: " . self::EXECUTABLE_BINARY_WHICH . " is not present in the shell!";
            InstallerLogger::addLogEntry($message);
            throw new Exception($message);
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
     * @param string $calledCommand
     * @return array
     */
    protected static function executeShellCommandWithFullOutputLinesAndCodeAsArray(string $calledCommand): array
    {
        exec($calledCommand . "  2>&1", $outputLines, $outputCode);
        return [
            self::KEY_EXECUTED_COMMAND => $calledCommand,
            self::KEY_OUTPUT_RESULT    => $outputLines,
            self::KEY_OUTPUT_CODE      => $outputCode,
            self::KEY_OUTPUT_SUCCESS   => ($outputCode == self::EXEC_CODE_SUCCESS),
        ];
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