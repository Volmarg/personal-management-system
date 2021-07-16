<?php

namespace Installer\Services\Shell;

use Installer\Controller\Installer\InstallerController;
use Exception;
use Installer\Services\InstallerLogger;

// for compatibility with AutoInstaller
if( "cli" !== php_sapi_name() )
{
    include_once("../installer/Services/Shell/ShellAbstractService.php");
    include_once("../installer/Services/Shell/ShellPhpService.php");
    include_once("../installer/Controller/InstallerController.php");
    include_once("../installer/Services/InstallerLogger.php");
}

/**
 * Handles shell calls to bin/console
 *
 * Class ShellBinConsoleService
 * @package App\Services\Shell
 */
class ShellBinConsoleService extends ShellAbstractService
{
    const EXECUTABLE_BINARY_NAME = "bin/console";

    const DOCTRINE_MESSAGE_DATABASE_DOES_NOT_EXIST = "Database doesn't exist";
    const DOCTRINE_MESSAGE_DROPPED_DATABASE        = "Dropped database";

    const ENCRYPTION_KEY_REGEX = "#\[OK\] (?<KEY>(.*))#";
    const REGEX_MATCH_KEY      = "KEY";

    const DOCTRINE_DROP_DATABASE_SUCCESS_MESSAGES = [
        self::DOCTRINE_MESSAGE_DATABASE_DOES_NOT_EXIST,
        self::DOCTRINE_MESSAGE_DROPPED_DATABASE,
    ];

    public static function getExecutableBinaryName(): string
    {
        return self::EXECUTABLE_BINARY_NAME;
    }

    /**
     * Will drop database
     *
     * @return bool success or false
     * @throws Exception
     */
    public static function dropDatabase(): bool
    {
        $callback = function(){
            $phpBinaryExecutable = ShellPhpService::getExecutableBinaryName();
            $binaryName          = self::getExecutableBinaryName();

            $executedCommand = $phpBinaryExecutable . " " . $binaryName . " doctrine:database:drop -n --force";
            $result          = self::executeShellCommandWithFullOutputLinesAndCodeAsArray($executedCommand);

            $outputLines = $result[self::KEY_OUTPUT_RESULT];
            foreach($outputLines as $outputLine){
                foreach(self::DOCTRINE_DROP_DATABASE_SUCCESS_MESSAGES as $successMessage){
                    if( stristr($outputLine, $successMessage) ){
                        return true;
                    }
                }
            }

            InstallerLogger::addLogEntry("Dropping database was not successful", [
                "commandResult" => $result
            ]);
            return false;
        };

        $callbackResult = InstallerController::executeCallbackWithSupportOfDirectoryChange($callback);
        return $callbackResult;
    }

    /**
     * Will create database
     *
     * @return bool success or false
     * @throws Exception
     */
    public static function createDatabase(): bool
    {
        $callbackResult = self::executeCommandAndCheckResultCode("doctrine:database:create");
        return $callbackResult;
    }

    /**
     * Will handle migrations
     *
     * @return bool success or false
     * @throws Exception
     */
    public static function executeMigrations(): bool
    {
        $callbackResult = self::executeCommandAndCheckResultCode("doctrine:migrations:migrate");
        return $callbackResult;
    }

    /**
     * Will clear cache
     *
     * @return bool success or false
     * @throws Exception
     */
    public static function clearCache(): bool
    {
        $callbackResult = self::executeCommandAndCheckResultCode("cache:clear");
        return $callbackResult;
    }

    /**
     * Will build cache
     *
     * @return bool success or false
     * @throws Exception
     */
    public static function buildCache(): bool
    {
        $callbackResult = self::executeCommandAndCheckResultCode("cache:warmup");
        return $callbackResult;
    }

    /**
     * This function will generate the key used for encrypting passwords
     * @throws Exception
     */
    public static function generateEncryptionKey(): ?string {

        $callback = function(): ?string {
            $phpBinaryExecutable = ShellPhpService::getExecutableBinaryName();
            $binaryName          = self::getExecutableBinaryName();

            $executedCommand = $phpBinaryExecutable . " " . $binaryName . " --env=dev encrypt:genkey -n";
            $result          = self::executeShellCommandWithFullOutputLinesAndCodeAsArray($executedCommand);

            $outputLines = $result[self::KEY_OUTPUT_RESULT];
            foreach($outputLines as $outputLine){
                if( preg_match(self::ENCRYPTION_KEY_REGEX, $outputLine, $matches) ){
                    return $matches[self::REGEX_MATCH_KEY];
                }
            }

            InstallerLogger::addLogEntry("Getting encryption key was not successful", [
                "commandResult" => $result
            ]);

            return null;
        };

        $callbackResult = InstallerController::executeCallbackWithSupportOfDirectoryChange($callback);
        return $callbackResult;
    }

    /**
     * Will execute command and check it's output code (0 is success)
     *
     * @return bool - true for success, else false
     * @throws Exception
     */
    private static function executeCommandAndCheckResultCode(string $commandToExecute): bool
    {
        $callback = function() use($commandToExecute): bool {
            $phpBinaryExecutable = ShellPhpService::getExecutableBinaryName();
            $binaryName          = self::getExecutableBinaryName();

            $executedCommand = $phpBinaryExecutable . " " . $binaryName . " $commandToExecute -n";
            $result          = self::executeShellCommandWithFullOutputLinesAndCodeAsArray($executedCommand);

            $code      = $result[self::KEY_OUTPUT_CODE];
            $isSuccess = (0 == $code);

            if(!$isSuccess){
                InstallerLogger::addLogEntry("Failed executing command", [
                    "commandResult" => $result
                ]);
            }

            return $isSuccess;
        };

        $callbackResult = InstallerController::executeCallbackWithSupportOfDirectoryChange($callback);
        return $callbackResult;
    }
}