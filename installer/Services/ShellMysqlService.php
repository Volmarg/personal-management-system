<?php

namespace Installer\Services\Shell;

use Exception;
use PDO;
use TypeError;

// for compatibility with AutoInstaller
if( "cli" !== php_sapi_name() ) {
    include_once("../installer/Services/ShellAbstractService.php");
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
     * Get sql mode of database
     *
     * @Return string
     */
    public static function getSqlMode(string $login, string $password): string
    {
        $commandToExecute = "mysql -u " . $login .  " --password=" . $password .  " --execute='SELECT @@sql_mode' 2> /dev/null | grep " . self::MYSQL_MODE_ONLY_FULL_GROUP_BY;
        $commandResult    = shell_exec($commandToExecute);

        return $commandResult;
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
            return false;
        }
    }

}