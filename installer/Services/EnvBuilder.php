<?php

namespace Installer\Services\Shell;

// for compatibility with AutoInstaller
if( "cli" !== php_sapi_name() ) {
    include_once("../installer/Services/InstallerLogger.php");
}

use Exception;
use Installer\Controller\Installer\InstallerController;
use Installer\Services\InstallerLogger;
use TypeError;

/**
 * Handles building env file
 *
 * Class EnvBuilder
 * @package App\Services\Shell
 */
class EnvBuilder
{
    const ENV_FILE_NAME = ".env";

    const SYSTEM_LOCK_SESSION_LIFETIME = 900;
    const USER_LOGIN_SESSION_LIFETIME  = 1800;

    const MAILER_URL = 'null://localhost';
    const APP_SECRET = 'b9abc19ae10d53eb7cf5b5684ec6511f';

    const PUBLIC_DIR        = 'public';

    const UPLOAD_DIR            = 'upload';
    const UPLOAD_DIR_IMAGES     = 'upload/images';
    const UPLOAD_DIR_FILES      = 'upload/files';
    const UPLOAD_DIR_VIDEOS     = 'upload/videos';
    const UPLOAD_DIR_MINIATURES = 'upload/miniatures';

    const IPS_ACCESS_RESTRICTION            = "[]";
    const NPL_DEFAULT_RECEIVER              = '[\"your@email.com\"]';
    const DEFAULT_EMERGENCY_EMAILS_RECEIVER = 'your@email.com';
    const DEFAULT_APP_IS_INSTALLED          = 1;

    const ENV_DEV   = "dev";
    const ENV_PROD  = "prod";

    const ENV_KEY_APP_ENV                             = 'APP_ENV';
    const ENV_KEY_APP_DEBUG                           = 'APP_DEBUG';
    const ENV_KEY_APP_SECRET                          = 'APP_SECRET';
    const ENV_KEY_APP_DEMO                            = 'APP_DEMO';
    const ENV_KEY_APP_MAINTENANCE                     = 'APP_MAINTENANCE';
    const ENV_KEY_APP_GUIDE                           = 'APP_GUIDE';
    const ENV_KEY_MAILER_URL                          = 'MAILER_URL';
    const ENV_KEY_DATABASE_URL                        = 'DATABASE_URL';
    const ENV_KEY_UPLOAD_DIR                          = 'UPLOAD_DIR';
    const ENV_KEY_IMAGES_UPLOAD_DIR                   = 'IMAGES_UPLOAD_DIR';
    const ENV_KEY_FILES_UPLOAD_DIR                    = 'FILES_UPLOAD_DIR';
    const ENV_KEY_VIDEOS_UPLOAD_DIR                   = 'VIDEOS_UPLOAD_DIR';
    const ENV_KEY_MINIATURES_UPLOAD_DIR               = 'MINIATURES_UPLOAD_DIR';
    const ENV_KEY_PUBLIC_ROOT_DIR                     = 'PUBLIC_ROOT_DIR';
    const ENV_KEY_APP_USER_LOGIN_SESSION_LIFETIME     = 'APP_USER_LOGIN_SESSION_LIFETIME';
    const ENV_KEY_APP_SYSTEM_LOCK_SESSION_LIFETIME    = 'APP_SYSTEM_LOCK_SESSION_LIFETIME';
    const ENV_KEY_APP_IPS_ACCESS_RESTRICTION          = 'APP_IPS_ACCESS_RESTRICTION';
    const ENV_KEY_APP_SHOW_INFO_BLOCKS                = 'APP_SHOW_INFO_BLOCKS';
    const ENV_KEY_APP_DEFAULT_NPL_RECEIVER_EMAILS     = 'APP_DEFAULT_NPL_RECEIVER_EMAILS';
    const ENV_KEY_APP_EMERGENCY_EMAILS_RECEIVER_EMAIL = 'APP_EMERGENCY_EMAILS_RECEIVER_EMAIL';
    const ENV_KEY_APP_IS_INSTALLED                    = 'APP_IS_INSTALLED';

    /**
     * Will build entire base .env file
     *
     * @param string $dbLogin
     * @param string $dbPassword
     * @param string $dbHost
     * @param string $dbPort
     * @param string $dbName
     * @param string $envMode
     * @param string $debugMode
     * @return bool - true on success false on fail
     */
    public static function buildEnv(string $dbLogin, string $dbPassword, string $dbHost, string $dbPort, string $dbName, string $envMode = self::ENV_PROD, string $debugMode = "false"): bool
    {
        try{
            $fileHandler = fopen(self::ENV_FILE_NAME, 'w+');
            {
                $databaseUrl = self::buildDatabaseConnectionUrl($dbLogin, $dbPassword, $dbHost, $dbPort, $dbName);

                fwrite($fileHandler,self::ENV_KEY_APP_ENV      . "="  . $envMode          . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_APP_DEBUG    . "="  . $debugMode        . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_APP_SECRET   . "="  . self::APP_SECRET  . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_MAILER_URL   . "="  . self::MAILER_URL  . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_DATABASE_URL . "="  . $databaseUrl      . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_UPLOAD_DIR   . "="  . self::UPLOAD_DIR  . PHP_EOL);

                fwrite($fileHandler,self::ENV_KEY_APP_GUIDE       . "="  . "false"  . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_APP_DEMO        . "="  . "false"  . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_APP_MAINTENANCE . "="  . "false"  . PHP_EOL);

                fwrite($fileHandler,self::ENV_KEY_IMAGES_UPLOAD_DIR     . "="  . self::UPLOAD_DIR_IMAGES      . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_FILES_UPLOAD_DIR      . "="  . self::UPLOAD_DIR_FILES       . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_VIDEOS_UPLOAD_DIR     . "="  . self::UPLOAD_DIR_VIDEOS      . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_MINIATURES_UPLOAD_DIR . "="  . self::UPLOAD_DIR_MINIATURES  . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_PUBLIC_ROOT_DIR       . "="  . self::PUBLIC_DIR             . PHP_EOL);

                fwrite($fileHandler,self::ENV_KEY_APP_USER_LOGIN_SESSION_LIFETIME     . "="  . self::USER_LOGIN_SESSION_LIFETIME       . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_APP_SYSTEM_LOCK_SESSION_LIFETIME    . "="  . self::SYSTEM_LOCK_SESSION_LIFETIME      . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_APP_IPS_ACCESS_RESTRICTION          . "="  . self::IPS_ACCESS_RESTRICTION            . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_APP_SHOW_INFO_BLOCKS                . "="  . "true"                                  . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_APP_DEFAULT_NPL_RECEIVER_EMAILS     . "="  . self::NPL_DEFAULT_RECEIVER              . PHP_EOL);
                fwrite($fileHandler,self::ENV_KEY_APP_EMERGENCY_EMAILS_RECEIVER_EMAIL . "="  . self::DEFAULT_EMERGENCY_EMAILS_RECEIVER . PHP_EOL);
            }
            fclose($fileHandler);

            return true;
        }catch(Exception | TypeError $e){
            InstallerLogger::addLogEntry("Could not build env file", [
                "exceptionMessage" => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Will add new variable to the .env file, however WILL NOT check if variable is already present
     *
     * @param string $variableName
     * @param string $variableValue
     */
    public static function addVariableToEnvFile(string $variableName, string $variableValue): void
    {
        $fileHandler = fopen(self::ENV_FILE_NAME, 'a+');
        {
            fwrite($fileHandler,PHP_EOL . $variableName . "=" . $variableValue . PHP_EOL);
        }
        fclose($fileHandler);
    }

    /**
     * Will return database url used in .env file
     *
     * @param string $dbLogin
     * @param string $dbPassword
     * @param string $dbHost
     * @param string $dbPort
     * @param string $dbName
     * @return string
     */
    private static function buildDatabaseConnectionUrl(string $dbLogin, string $dbPassword, string $dbHost, string $dbPort, string $dbName): string
    {
        $databaseUrl = "mysql://{$dbLogin}:{$dbPassword}@{$dbHost}:{$dbPort}/{$dbName}";
        return $databaseUrl;
    }

    /**
     * Will remove env file
     */
    public static function removeEnvFile(): void
    {
            InstallerLogger::addLogEntry("Removing env file");
            {
                $callback = function(){

                    $isRemoved = true; // if doesnt exist then it's success anyway
                    if( file_exists(self::ENV_FILE_NAME) ){
                        $isRemoved = unlink(self::ENV_FILE_NAME);
                    }

                    return $isRemoved;
                };
                $isRemoved= InstallerController::executeCallbackWithSupportOfDirectoryChange($callback);
            InstallerLogger::addLogEntry("Done removing env file", ["status" => $isRemoved]);
        }
    }
}