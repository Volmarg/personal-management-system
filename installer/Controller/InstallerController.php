<?php

namespace Installer\Controller\Installer;

// for compatibility with AutoInstaller
if( "cli" !== php_sapi_name() ) {
    include_once("../installer/Services/Shell/ShellMysqlService.php");
    include_once("../installer/Services/Shell/ShellPhpService.php");
    include_once("../installer/Services/Shell/ShellComposerService.php");
    include_once("../installer/Services/Shell/ShellBinConsoleService.php");
    include_once("../installer/Services/InstallerLogger.php");
    include_once("../installer/Services/EnvBuilder.php");
    include_once("../installer/DTO/DatabaseDataDTO.php");
}

use Installer\Controller\DTO\DatabaseDataDTO;
use App\Services\Files\Parser\YamlFileParserService;
use Installer\Services\Shell\EnvBuilder;
use Installer\Services\InstallerLogger;
use Installer\Services\Shell\ShellBinConsoleService;
use Installer\Services\Shell\ShellComposerService;
use Installer\Services\Shell\ShellMysqlService;
use Installer\Services\Shell\ShellPhpService;
use Exception;
use TypeError;

/**
 * Handler of installer logic
 */
class InstallerController
{

    const PRODUCTION_REQUIREMENT_MYSQL               = "Mysql executable exists";
    const PRODUCTION_REQUIREMENT_MYSQL_ACCESS_VALID  = "Mysql access valid";
    const PRODUCTION_REQUIREMENT_MYSQL_MODE_DISABLED = "Mysql mode has " . ShellMysqlService::MYSQL_MODE_ONLY_FULL_GROUP_BY . " disabled";
    const PRODUCTION_REQUIREMENT_PHP                 = "Php7.4 installed";
    const PRODUCTION_REQUIREMENT_COMPOSER            = "Composer global executable exists";

    const CONFIGURE_PREPARE_COMPOSER_PACKAGES        = "Composer packages";
    const CONFIGURE_PREPARE_ENV_FILE                 = "Env file";
    const CONFIGURE_PREPARE_CREATE_FOLDERS           = "Create folders";
    const CONFIGURE_PREPARE_DROP_DATABASE_IF_EXISTS  = "Drop database if exists";
    const CONFIGURE_PREPARE_CREATE_DATABASE          = "Create database";
    const CONFIGURE_PREPARE_BUILD_DATABASE_STRUCTURE = "Build database structure";
    const CONFIGURE_PREPARE_CLEAR_CACHE              = "Clear cache";
    const CONFIGURE_PREPARE_BUILD_CACHE              = "Build cache";
    const CONFIGURE_PREPARE_GENERATE_ENCRYPTION_KEY  = "Generate encryption key";
    const CONFIGURE_PREPARE_SAVE_ENCRYPTION_KEY      = "Save encryption key";

    const FOLDER_VAR_CACHE = "var/cache";
    const USER_WWW_DATA    = "www-data";

    const CONFIG_ENCRYPTION_YAML_PATH       = "config/packages/config/encryption.yaml";
    const CONFIG_ENCRYPTION_KEY_ENCRYPT_KEY = "parameters.encrypt_key";

    /**
     * Will return array of production based environments data, with information if conditions are meet or not
     *
     * @return array
     * @throws Exception
     */
    public static function checkProductionBasedRequirements(): array
    {
        InstallerLogger::clearLogFile();
        InstallerLogger::addLogEntry("Started checking production requirements, starting with mysql and php");

        $isMysqlInstalled            = ShellMysqlService::isExecutableForServicePresent();
        $isProperPhpVersionInstalled = ShellPhpService::isProperPhpVersion();

        $returnedData = [
            self::PRODUCTION_REQUIREMENT_PHP   => $isProperPhpVersionInstalled,
            self::PRODUCTION_REQUIREMENT_MYSQL => $isMysqlInstalled,
        ];

        InstallerLogger::addLogEntry("Checking for mysql and php version is done", $returnedData);

        if($isMysqlInstalled){
            $requestJson     = file_get_contents("php://input");
            $databaseDataDto = DatabaseDataDTO::fromJson($requestJson);

            InstallerLogger::addLogEntry("Checking if database access is valid");

            $isDbPasswordValid = ShellMysqlService::isDbAccessValid(
                $databaseDataDto->getDatabaseLogin(),
                $databaseDataDto->getDatabaseHost(),
                $databaseDataDto->getDatabasePort(),
                $databaseDataDto->getDatabasePassword()
            );
            $returnedData[self::PRODUCTION_REQUIREMENT_MYSQL_ACCESS_VALID] = $isDbPasswordValid;

            InstallerLogger::addLogEntry("Done checking if database access is valid", [ "status" => $isDbPasswordValid]);

            if($isDbPasswordValid){
                InstallerLogger::addLogEntry("Started checking ONLY_FULL_GROUP_BY mode");

                $isOnlyFullGroupByMysqlModeSet = ShellMysqlService::isOnlyFullGroupByMysqlModeDisabled(
                    $databaseDataDto->getDatabaseLogin(),
                    $databaseDataDto->getDatabaseHost(),
                    $databaseDataDto->getDatabasePort(),
                    $databaseDataDto->getDatabasePassword()
                );

                InstallerLogger::addLogEntry("Done checking ONLY_FULL_GROUP_BY mode", [ "status" => $isOnlyFullGroupByMysqlModeSet]);

                $returnedData[self::PRODUCTION_REQUIREMENT_MYSQL_MODE_DISABLED] = $isOnlyFullGroupByMysqlModeSet;
            }

        }
        InstallerLogger::addLogEntry("Done checking environment ");

        return $returnedData;
    }

    /**
     * Will execute configuration / system preparation logic
     *
     * @return array
     * @throws Exception
     */
    public static function configureAndPrepareSystem(): array
    {
        $resultData      = [];
        $requestJson     = file_get_contents("php://input");
        $databaseDataDto = DatabaseDataDTO::fromJson($requestJson);

        InstallerLogger::addLogEntry("Started configuring and preparing system");
        InstallerLogger::addLogEntry("Installing composer packages");
        {
            $isComposerInstallSuccess = ShellComposerService::installPackages();
            $resultData[self::CONFIGURE_PREPARE_COMPOSER_PACKAGES] = $isComposerInstallSuccess;
        }
        InstallerLogger::addLogEntry("Done installing composer packages", ["status" => $isComposerInstallSuccess]);
        if(!$isComposerInstallSuccess){
            return $resultData;
        }

        InstallerLogger::addLogEntry("Started building env file");
        {
            $createEnvCallback = function() use($databaseDataDto): bool {
                $isEnvFileCreated = EnvBuilder::buildEnv(
                    $databaseDataDto->getDatabaseLogin(),
                    $databaseDataDto->getDatabasePassword(),
                    $databaseDataDto->getDatabaseHost(),
                    $databaseDataDto->getDatabasePort(),
                    $databaseDataDto->getDatabaseName()
                );

                return $isEnvFileCreated;
            };

            $isEnvFileCreated = self::executeCallbackWithSupportOfDirectoryChange($createEnvCallback);
            $resultData[self::CONFIGURE_PREPARE_ENV_FILE] = $isEnvFileCreated;
        }
        InstallerLogger::addLogEntry("Done building env file", ["status" => $isEnvFileCreated]);
        if(!$isEnvFileCreated){
            return $resultData;
        }

        InstallerLogger::addLogEntry("Started creating folders");
        {
            $areFoldersCreated = self::createFolders();
            $resultData[self::CONFIGURE_PREPARE_CREATE_FOLDERS] = $areFoldersCreated;
        }
        InstallerLogger::addLogEntry("Done creating folders", ["status" => $areFoldersCreated]);
        if(!$areFoldersCreated){
            return $resultData;
        }

        InstallerLogger::addLogEntry("Started dropping database");
        {
            $isDatabaseDroppedIfExists = ShellBinConsoleService::dropDatabase();
            $resultData[self::CONFIGURE_PREPARE_DROP_DATABASE_IF_EXISTS] = $isDatabaseDroppedIfExists;
        }
        InstallerLogger::addLogEntry("Finished dropping database", ["status" => $isDatabaseDroppedIfExists]);
        if(!$isDatabaseDroppedIfExists){
            return $resultData;
        }

        InstallerLogger::addLogEntry("Started creating database");
        {
            $isDatabaseCreated = ShellBinConsoleService::createDatabase();
            $resultData[self::CONFIGURE_PREPARE_CREATE_DATABASE] = $isDatabaseCreated;
        }
        InstallerLogger::addLogEntry("Done creating database", ["status" => $isDatabaseCreated]);
        if(!$isDatabaseCreated){
            return $resultData;
        }

        InstallerLogger::addLogEntry("Started executing migrations");
        {
            $isDatabaseStructureBuilt = ShellBinConsoleService::executeMigrations();
            $resultData[self::CONFIGURE_PREPARE_BUILD_DATABASE_STRUCTURE] = $isDatabaseStructureBuilt;
        }
        InstallerLogger::addLogEntry("Finished executing migrations", ["status" => $isDatabaseStructureBuilt]);
        if(!$isDatabaseStructureBuilt){
            return $resultData;
        }

        InstallerLogger::addLogEntry("Started generating encryption key");
        {
            $generatedEncryptionKey = ShellBinConsoleService::generateEncryptionKey();
            $resultData[self::CONFIGURE_PREPARE_GENERATE_ENCRYPTION_KEY] = !empty($generatedEncryptionKey);
        }
        InstallerLogger::addLogEntry("Finished generating encryption key", ["status" => !empty($generatedEncryptionKey)]);
        if( empty($generatedEncryptionKey) ){
            return $resultData;
        }

        InstallerLogger::addLogEntry("Started saving encryption key");
        {
            $isEncryptionKeySaved = self::setEncryptionKey($generatedEncryptionKey);
            $resultData[self::CONFIGURE_PREPARE_SAVE_ENCRYPTION_KEY] = $isEncryptionKeySaved;
        }
        InstallerLogger::addLogEntry("Finished saving encryption key", ["status" => $isEncryptionKeySaved]);
        if( empty($isEncryptionKeySaved) ){
            return $resultData;
        }

        InstallerLogger::addLogEntry("Started clearing cache");
        {
            $isCacheCleared = ShellBinConsoleService::clearCache();
            $resultData[self::CONFIGURE_PREPARE_CLEAR_CACHE] = $isCacheCleared;
        }
        InstallerLogger::addLogEntry("Finished clearing cache", ["status" => $isCacheCleared]);
        if(!$isCacheCleared){
            return $resultData;
        }

        InstallerLogger::addLogEntry("Started building cache");
        {
            $isCacheBuilt = ShellBinConsoleService::buildCache();
            $resultData[self::CONFIGURE_PREPARE_BUILD_CACHE] = $isCacheBuilt;
        }
        InstallerLogger::addLogEntry("Finished building cache", ["status" => $isCacheBuilt]);
        if(!$isCacheBuilt){
            return $resultData;
        }

        InstallerLogger::addLogEntry("Now marking installation as finished");
        {
            self::setEnvKeyAppIsInstalled();
        }
        InstallerLogger::addLogEntry("Installation finished!");

        return $resultData;
    }

    /**
     * Will attempt to create folders, send created/existing folders array
     *
     * @return bool
     */
    public static function createFolders(): bool
    {
        $callback = function(): bool {

            $uploadDir       = EnvBuilder::PUBLIC_DIR . DIRECTORY_SEPARATOR . EnvBuilder::UPLOAD_DIR;
            $uploadFilesDir  = EnvBuilder::PUBLIC_DIR . DIRECTORY_SEPARATOR . EnvBuilder::UPLOAD_DIR_FILES;
            $uploadImagesDir = EnvBuilder::PUBLIC_DIR . DIRECTORY_SEPARATOR . EnvBuilder::UPLOAD_DIR_IMAGES;
            $uploadVideosDir = EnvBuilder::PUBLIC_DIR . DIRECTORY_SEPARATOR . EnvBuilder::UPLOAD_DIR_VIDEOS;

            try{
                if( !file_exists($uploadDir) ){
                    mkdir($uploadDir);
                    chown($uploadDir, self::USER_WWW_DATA);
                    chgrp($uploadDir, self::USER_WWW_DATA);
                }

                if( !file_exists($uploadFilesDir) ){
                    mkdir($uploadFilesDir);
                    chown($uploadFilesDir, self::USER_WWW_DATA);
                    chgrp($uploadFilesDir, self::USER_WWW_DATA);
                }

                if( !file_exists($uploadImagesDir) ){
                    mkdir($uploadImagesDir);
                    chown($uploadImagesDir, self::USER_WWW_DATA);
                    chgrp($uploadImagesDir, self::USER_WWW_DATA);
                }

                if( !file_exists($uploadVideosDir) ){
                    mkdir($uploadVideosDir);
                    chown($uploadVideosDir, self::USER_WWW_DATA);
                    chgrp($uploadVideosDir, self::USER_WWW_DATA);
                }

                $callback = function(): void {
                    if( !file_exists(self::FOLDER_VAR_CACHE) ){
                        mkdir(self::FOLDER_VAR_CACHE, 0777, true);
                        chown(self::FOLDER_VAR_CACHE, self::USER_WWW_DATA);
                        chgrp(self::FOLDER_VAR_CACHE, self::USER_WWW_DATA);
                    }
                };

                self::executeCallbackWithSupportOfDirectoryChange($callback);

                return true;
            }catch(Exception | TypeError $e){
                return false;
            }

        };

        $callbackResult = self::executeCallbackWithSupportOfDirectoryChange($callback);
        return $callbackResult;
    }

    /**
     * Will set env key marking installation as done
     */
    public static function setEnvKeyAppIsInstalled(): void {
        $callback = function(): void {
            /**
             * Must be here because with the command the dir changes to root dir of project
             * The inclusion belongs explicitly to the callback
             */
            include_once("vendor/autoload.php");
            EnvBuilder::addVariableToEnvFile(EnvBuilder::ENV_KEY_APP_IS_INSTALLED, EnvBuilder::DEFAULT_APP_IS_INSTALLED);
        };

        self::executeCallbackWithSupportOfDirectoryChange($callback);
    }

    /**
     * Will save the encryption key to file
     *
     * @param string $encryptionKey
     * @return bool
     */
    public static function setEncryptionKey(string $encryptionKey): bool
    {
        $callback = function() use($encryptionKey): bool {
            /**
             * Must be here because with the command the dir changes to root dir of project
             * The inclusion belongs explicitly to the callback
             */
            include_once("vendor/autoload.php");

            $isEncryptionKeySaved = YamlFileParserService::replaceArrayNodeValue(self::CONFIG_ENCRYPTION_KEY_ENCRYPT_KEY, $encryptionKey, self::CONFIG_ENCRYPTION_YAML_PATH);
            return $isEncryptionKeySaved;
        };

        $isEncryptionKeySaved = self::executeCallbackWithSupportOfDirectoryChange($callback);
        return $isEncryptionKeySaved;
    }

    /**
     * Some logic requires to go to root project dir,
     * but then it's needed to go back to work properly with all the `includes` etc
     *
     * So this method will go to project root dir, execute callback and comes back in dir structure,
     *
     * @param callable $callback
     * @return mixed
     */
    public static function executeCallbackWithSupportOfDirectoryChange(callable $callback)
    {
        /**
         * For cli just execute the code
         */
        if( "cli" === php_sapi_name() ){
            $callbackResult = $callback();
            return $callbackResult;
        }

        /**
         * For GUI based logic dir must be changed before executing code
         */
        $previousDirectory = getcwd();
        $rootDirectory     = $previousDirectory . "/../";

        chdir($rootDirectory);
        {
            $callbackResult = $callback();
        }
        chdir($previousDirectory);

        return $callbackResult;
    }

    /**
     * Will check if the installer has completed the installation
     *
     * @param string $envFilePath
     * @return bool
     */
    public static function isInstalled(string $envFilePath): bool
    {
        if(
                file_exists($envFilePath)
            &&  strstr(file_get_contents($envFilePath), EnvBuilder::ENV_KEY_APP_IS_INSTALLED)
        ){
           return true;
        }

        return false;
    }

    /**
     * Will check if project was already installed
     *
     * @param string $envFilePath
     * @return bool
     */
    public static function wasAlreadyInstalled(string $envFilePath): bool
    {
        if(
                file_exists($envFilePath)
            &&  !strstr(file_get_contents($envFilePath), EnvBuilder::ENV_KEY_APP_IS_INSTALLED)
            &&  file_exists("../vendor")
        ){
            return true;
        }

        return false;
    }

}