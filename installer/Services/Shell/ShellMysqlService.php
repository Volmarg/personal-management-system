<?php

namespace Installer\Services\Shell;

use Exception;
use Installer\Services\InstallerLogger;
use PDO;
use TypeError;

// for compatibility with AutoInstaller
if( "cli" !== php_sapi_name() ) {
    include_once("../installer/Services/Shell/ShellAbstractService.php");
    include_once("../installer/Services/InstallerLogger.php");
}

/**
 * Handles shell calls to php
 *
 * Class ShellPhpService
 * @package App\Services\Shell
 */
class ShellMysqlService extends ShellAbstractService
{
    const EXECUTABLE_BINARY_NAME        = "mysql";
    const MYSQL_MODE_ONLY_FULL_GROUP_BY = "ONLY_FULL_GROUP_BY";

    /**
     * Will return executable php binary name
     * @Return string
     */
    public static function getExecutableBinaryName(): string
    {
        return self::EXECUTABLE_BINARY_NAME;
    }

    /**
     * Will check if db access db valid
     *
     * @param string $dbLogin
     * @param string $dbHost
     * @param string $dbPort
     * @param string|null $dbPassword
     * @return bool
     */
    public static function isDbAccessValid(string $dbLogin, string $dbHost, string $dbPort, ?string $dbPassword = null): bool
    {
        // needed for compatibility with AutoInstaller
        if( empty($dbPassword) ){
            return false;
        }

        $pdoDsn = "mysql:host={$dbHost};port={$dbPort}";

        try{
            $pdo = new PDO($pdoDsn, $dbLogin, $dbPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }catch(Exception | TypeError $e){
            InstallerLogger::addLogEntry("Something went wrong while checking db access", [
                "message" => $e->getMessage(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Will check if the db has proper mode
     *
     * @param string $dbLogin
     * @param string $dbHost
     * @param string $dbPort
     * @param string|null $dbPassword
     * @return bool
     */
    public static function isOnlyFullGroupByMysqlModeDisabled(string $dbLogin, string $dbHost, string $dbPort, ?string $dbPassword = null): bool
    {
        $pdoDsn = "mysql:host={$dbHost};port={$dbPort}";

        try{
            $pdo = new PDO($pdoDsn, $dbLogin, $dbPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $statement = $pdo->query("SELECT @@sql_mode AS mode");
            $row       = $statement->fetch(PDO::FETCH_ASSOC);
            $mode      = $row['mode'];

            return !strstr($mode, self::MYSQL_MODE_ONLY_FULL_GROUP_BY);
        }catch(Exception | TypeError $e){
            InstallerLogger::addLogEntry("Something went wrong while checking db mode", [
                "message" => $e->getMessage(),
            ]);
            return false;
        }
    }

}