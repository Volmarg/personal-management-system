<?php

namespace Installer\Services;

use DateTime;

/**
 * Handles logging explicitly for installer
 *
 * Class InstallerLogger
 * @package Installer\Services\Shell
 */
class InstallerLogger
{
    const ATTEMPTS_TO_GET_LOG_FILE = 5;
    const LOG_FILE_PATH            = "installer/installer.log";

    /**
     * Will add log installer log entry
     *
     * @param string $message
     * @param array $data
     */
    public static function addLogEntry(string $message, array $data = []): void
    {
        $logFilePath  = self::findLogFilePath();
        $dateStamp    = "[" . (new DateTime())->format("Y-m-d H:i:s") . "] ";
        $addedMessage = $dateStamp . $message . " [ " . json_encode($data) . " ] " . PHP_EOL;
        error_log($addedMessage, 3, $logFilePath);
    }

    /**
     * Will clear the log file content
     */
    public static function clearLogFile(): void
    {
        $logFilePath  = self::findLogFilePath();
        file_put_contents($logFilePath, "");
    }

    /**
     * Will return log file path - will try few times to find correct path
     *
     * @return string
     */
    public static function findLogFilePath(): string
    {
        $attemptNumber = 1;
        $logFilePath   = self::LOG_FILE_PATH;
        while(
                !file_exists($logFilePath)
            &&  $attemptNumber <= self::ATTEMPTS_TO_GET_LOG_FILE
        ){
            $logFilePath = "../" . $logFilePath;
            $attemptNumber++;
        }

        return $logFilePath;
    }

}