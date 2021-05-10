<?php

namespace App;

/**
 * This script is run from CLI (composer) but symfony will crash later that no such file is found
 * The same goes for the testCheck - this will require later to reimplementing check Env::isTest();
 */
if( php_sapi_name() === 'cli' ){

    if( !file_exists("vendor/autoload.php") ){
        throw new Exception("Composer autoload file was not found, did You called `composer install` earlier?");
    }

    include_once 'src/Controller/Utils/CliHandler.php';
    include_once 'vendor/autoload.php';
    include_once 'src/Services/Files/Parser/YamlFileParserService.php';
}

use App\Controller\Utils\CliHandler;
use App\Services\Files\Parser\YamlFileParserService;
use Exception;

/**
 * Namespace is omitted here as this is only used by composer
 * The coloring methods are here on purpose, can be extracted if needed, this in other cases Symfony has built in coloring
 * Class AutoInstaller
 */
class AutoInstaller{

    const YES = "Yes";
    const NO  = "No";

    const MAILER_URL = 'null://localhost';

    const MODE_DEVELOPMENT = 'Development';
    const MODE_PRODUCTION  = 'Production';

    const PUBLIC_DIR        = 'public';

    const UPLOAD_DIR            = 'upload';
    const UPLOAD_DIR_IMAGES     = 'upload/images';
    const UPLOAD_DIR_FILES      = 'upload/files';
    const UPLOAD_DIR_VIDEOS     = 'upload/videos';
    const UPLOAD_DIR_MINIATURES = 'upload/miniatures';

    const APP_SECRET        = 'b9abc19ae10d53eb7cf5b5684ec6511f';

    const SYSTEM_LOCK_SESSION_LIFETIME = 900;
    const USER_LOGIN_SESSION_LIFETIME  = 1800;
    const IPS_ACCESS_RESTRICTION       = "[]";
    const NPL_DEFAULT_RECEIVER         = '[\"your@email.com\"]';

    const ENV_DEV   = "dev";
    const ENV_PROD  = "prod";

    const ENV_KEY_APP_ENV                          = 'APP_ENV';
    const ENV_KEY_APP_DEBUG                        = 'APP_DEBUG';
    const ENV_KEY_APP_SECRET                       = 'APP_SECRET';
    const ENV_KEY_APP_DEMO                         = 'APP_DEMO';
    const ENV_KEY_APP_MAINTENANCE                  = 'APP_MAINTENANCE';
    const ENV_KEY_APP_GUIDE                        = 'APP_GUIDE';
    const ENV_KEY_MAILER_URL                       = 'MAILER_URL';
    const ENV_KEY_DATABASE_URL                     = 'DATABASE_URL';
    const ENV_KEY_UPLOAD_DIR                       = 'UPLOAD_DIR';
    const ENV_KEY_IMAGES_UPLOAD_DIR                = 'IMAGES_UPLOAD_DIR';
    const ENV_KEY_FILES_UPLOAD_DIR                 = 'FILES_UPLOAD_DIR';
    const ENV_KEY_VIDEOS_UPLOAD_DIR                = 'VIDEOS_UPLOAD_DIR';
    const ENV_KEY_MINIATURES_UPLOAD_DIR            = 'MINIATURES_UPLOAD_DIR';
    const ENV_KEY_PUBLIC_ROOT_DIR                  = 'PUBLIC_ROOT_DIR';
    const ENV_KEY_APP_USER_LOGIN_SESSION_LIFETIME  = 'APP_USER_LOGIN_SESSION_LIFETIME';
    const ENV_KEY_APP_SYSTEM_LOCK_SESSION_LIFETIME = 'APP_SYSTEM_LOCK_SESSION_LIFETIME';
    const ENV_KEY_APP_IPS_ACCESS_RESTRICTION       = 'APP_IPS_ACCESS_RESTRICTION';
    const ENV_KEY_APP_SHOW_INFO_BLOCKS             = 'APP_SHOW_INFO_BLOCKS';
    const ENV_KEY_APP_DEFAULT_NPL_RECEIVER_EMAILS  = 'APP_DEFAULT_NPL_RECEIVER_EMAILS';

    const CONFIG_ENCRYPTION_YAML_PATH       = "config/packages/config/encryption.yaml";
    const CONFIG_ENCRYPTION_KEY_ENCRYPT_KEY = "parameters.encrypt_key";

    const PHP_EXECUTABLE_DEFAULT = "php";
    const PHP_EXECUTABLE_7_4     = "php7.4";

    static $isNodeInstalled = false;

    static $isNpmInstalled = false;

    static $isMysqlInstalled = false;

    static $dependenciesState = [];

    static $mysqlPort     = '';

    static $mysqlHost     = '';

    static $mysqlLogin    = '';

    static $mysqlPassword = '';

    static $mysqlDatabase = '';

    static $userSelectedMode = '';

    static $phpExecutable = "";

    public static function runDocker(){

        self::definePhpExecutable();
        CliHandler::initialize();

        CliHandler::newLine();
        self::installerAreaLine();
        {
            self::getDependenciesInformation();

            self::writeGeneralInformation();

            CliHandler::lineSeparator();
            CliHandler::newLine();

            CliHandler::lineSeparator();
            self::setDatabase();

            CliHandler::lineSeparator();
            self::createFolders();

            CliHandler::lineSeparator();
            self::buildCache();

            CliHandler::lineSeparator();
            self::setPermissions();

            CliHandler::lineSeparator();
            self::generateEncryptionKey();

            self::writeEndInformation();
        }
        self::installerAreaLine();
        CliHandler::newLine();
    }
    
    public static function run(){

        self::definePhpExecutable();
        CliHandler::initialize();

        CliHandler::newLine();
        self::installerAreaLine();
        {
            self::getDependenciesInformation();

            self::writeGeneralInformation();
            self::writeDependenciesMessage();

            CliHandler::lineSeparator();
            CliHandler::newLine();

            self::askQuestions();

            CliHandler::lineSeparator();
            self::checkMysqlMode();

            CliHandler::lineSeparator();
            self::buildEnv();

            CliHandler::lineSeparator();
            self::setDatabase();

            CliHandler::lineSeparator();
            self::createFolders();

            CliHandler::lineSeparator();
            self::buildCache();

            CliHandler::lineSeparator();
            self::setPermissions();

            CliHandler::lineSeparator();
            self::installPackages();

            CliHandler::lineSeparator();
            self::generateEncryptionKey();

            self::writeEndInformation();
        }
        self::installerAreaLine();
        CliHandler::newLine();
    }

    /**
     * This function writes basic information like required space on hdd etc
     */
    private static function writeGeneralInformation(){

        CliHandler::infoText("
        #########################################################################################################################
        #    This script will install and configure the project - make sure that You run this command as sudo. Despite the fact # 
        #    that this installer should handle most of things You will still need to do some things manually on the end.        # 
        #    Keep in mind that this is a simple one time run script - it won't check if You have already packages installed.    # 
        #    Some things just cannot be configured from the script, like the mysql configuration file in case of problems.      #
        #    There will be few attempts to solve some of the problems but You might be asked to do the backup on Your own.      #
        #                                                                                                                       #
        #    Make sure that You've run 'composer install' as sudo - this is a requirement!                                      #
        #    Also call 'composer install' ONLY from within the project root dir, otherwise You might mess up Your system        #
        #    As there are permissions being set to 'var' folder which is a subfolder of symfony project                         #
        #                                                                                                                       #
        #   Please install the composer dependencies first if You haven't made this yet!                                        #
        #########################################################################################################################
        ");


        CliHandler::infoText("Basic requirements:" );
        CliHandler::lineSeparator();

        CliHandler::text("Internet connection" );
        CliHandler::text("Up to 500mb free space for production version" );
        CliHandler::text("Up to 1gb free space for development version" );

    }

    /**
     * This function write information about dependencies such as node/npm
     */
    private static function writeDependenciesMessage(){

        CliHandler::infoText("To install this project You need to have packages below installed");
        CliHandler::lineSeparator();

        CliHandler::text("Mysql: "  .  ( self::$isMysqlInstalled    ? CliHandler::successMark() : CliHandler::failureMark() ) );
        CliHandler::text("Node: "   .  ( self::$isNodeInstalled     ? CliHandler::successMark() : CliHandler::failureMark() ) );
        CliHandler::text("Npm: "    .  ( self::$isNpmInstalled      ? CliHandler::successMark() : CliHandler::failureMark() ) );

        if( in_array(false, self::$dependenciesState) ){
            CliHandler::errorText(PHP_EOL . "At least one of required packages is not present - please install it first");
        }
    }

    /**
     * This function will get all needed RAW information about dependencies
     */
    private static function getDependenciesInformation(){

        self::$isNodeInstalled   = trim(shell_exec('node -v'));
        self::$isNpmInstalled    = trim(shell_exec('npm -v'));
        self::$isMysqlInstalled  = trim(shell_exec('mysql -V'));

        self::$dependenciesState = [
            self::$isMysqlInstalled,
            self::$isNodeInstalled,
            self::$isNpmInstalled,
        ];

    }

    /**
     * This function is responsible for interaction with user - getting information from him/her
     */
    private static function askQuestions(){

        while( empty(self::$mysqlPort) ){
            self::$mysqlPort = CliHandler::getUserInput("What is Your database port?");
        }
        while( empty(self::$mysqlHost) ){
            self::$mysqlHost = CliHandler::getUserInput("What is Your database host?");
        }
        while( empty(self::$mysqlLogin) ){
            self::$mysqlLogin = CliHandler::getUserInput("What is Your database login?");
        }

        self::$mysqlPassword  = CliHandler::getUserInput("What is Your database password?"); //it can be empty

        while( empty(self::$mysqlDatabase) ){
            self::$mysqlDatabase = CliHandler::getUserInput("How do You want to name Your new database?");
        }

        self::$userSelectedMode = CliHandler::choices([
            self::MODE_DEVELOPMENT,
            self::MODE_PRODUCTION,
        ], "Which mode do You want to install?");

        CliHandler::errorText('!! Beware that if You provided existing database then it will be dropped. !!');

        $selectedOption = CliHandler::choices([
            self::YES,
            self::NO,
        ], "Do You want to continue?");

        if( self::NO === $selectedOption ){
            CliHandler::errorText("Well... ok");
            self::installerAreaLine();
            exit;
        };
    }

    /**
     * This function will build the basic version of env
     */
    private static function buildEnv(){
        CliHandler::infoText("Started building env file.");
        $envFileName = '.env';

        $dbLogin    = self::$mysqlLogin;
        $dbPassword = self::$mysqlPassword;
        $dbHost     = self::$mysqlHost;
        $dbPort     = self::$mysqlPort;
        $dbName     = self::$mysqlDatabase;

        $dbConnectionCheckCommand = "mysql -u {$dbLogin} -h {$dbHost} --port={$dbPort} -e\"quit\"";
        if( !empty($dbPassword) ){
            $dbConnectionCheckCommand .= " -p{$dbPassword}";
        }

        exec($dbConnectionCheckCommand, $output, $connectionCheckResult);

        $areCredentailValid = empty($connectionCheckResult); //returns nothing if credentials are ok
        if( !$areCredentailValid ){
            CliHandler::errorText("Database credentials are incorrect, could not connect - aborting.");
            CliHandler::infoText("Command used to check credentials: ". $dbConnectionCheckCommand);
            self::installerAreaLine();
            exit();
        }

        if( file_exists($envFileName) ){
            CliHandler::errorText("
                Env file already exist so It won't be modified - You must do this manually then. 
                Keep in mind that if the database connection is incorrect - this installer will then crash with PDO errors.
                That's because of how the migrations / doctrine commands works like. 
                Using database configuration from .env
                Please wait...
            ");

            sleep(6);
            self::installerAreaLine();
            return;
        }

        $databaseUrl = "mysql://{$dbLogin}:{$dbPassword}@{$dbHost}:{$dbPort}/{$dbName}";
        if( self::MODE_DEVELOPMENT === self::$userSelectedMode ){
            $env    = self::ENV_DEV;
            $debug  = "true";
        }else{
            $env    = self::ENV_PROD;
            $debug  = "false";
        }
        $fileHandler = fopen($envFileName, 'w+');
        {
            fwrite($fileHandler,self::ENV_KEY_APP_ENV      . "="  . $env              . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_APP_DEBUG    . "="  . $debug            . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_APP_SECRET   . "="  . self::APP_SECRET  . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_MAILER_URL   . "="  . self::MAILER_URL  . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_DATABASE_URL . "="  . $databaseUrl     . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_UPLOAD_DIR   . "="  . self::UPLOAD_DIR  . PHP_EOL);

            fwrite($fileHandler,self::ENV_KEY_APP_GUIDE       . "="  . "false"  . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_APP_DEMO        . "="  . "false"  . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_APP_MAINTENANCE . "="  . "false"  . PHP_EOL);

            fwrite($fileHandler,self::ENV_KEY_IMAGES_UPLOAD_DIR     . "="  . self::UPLOAD_DIR_IMAGES      . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_FILES_UPLOAD_DIR      . "="  . self::UPLOAD_DIR_FILES       . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_VIDEOS_UPLOAD_DIR     . "="  . self::UPLOAD_DIR_VIDEOS      . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_MINIATURES_UPLOAD_DIR . "="  . self::UPLOAD_DIR_MINIATURES  . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_PUBLIC_ROOT_DIR       . "="  . self::PUBLIC_DIR             . PHP_EOL);

            fwrite($fileHandler,self::ENV_KEY_APP_USER_LOGIN_SESSION_LIFETIME   . "="  . self::USER_LOGIN_SESSION_LIFETIME  . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_APP_SYSTEM_LOCK_SESSION_LIFETIME  . "="  . self::SYSTEM_LOCK_SESSION_LIFETIME . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_APP_IPS_ACCESS_RESTRICTION        . "="  . self::IPS_ACCESS_RESTRICTION       . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_APP_SHOW_INFO_BLOCKS              . "="  . "true"                             . PHP_EOL);
            fwrite($fileHandler,self::ENV_KEY_APP_DEFAULT_NPL_RECEIVER_EMAILS   . "="  . self::NPL_DEFAULT_RECEIVER         . PHP_EOL);
        }
        fclose($fileHandler);
        CliHandler::infoText('Env file has been created.');
    }

    /**
     * This function will create database
     */
    private static function setDatabase(){
        CliHandler::infoText("Started configuring the database.");
        {
            $dropDatabaseCommand   = self::$phpExecutable . " bin/console doctrine:database:drop -n --force";
            $createDatabaseCommand = self::$phpExecutable . " bin/console doctrine:database:create -n";
            $runMigrations         = self::$phpExecutable . " bin/console doctrine:migrations:migrate -n";

            shell_exec($dropDatabaseCommand);
            CliHandler::text("Database has been dropped (if You provided the existing one)");

            shell_exec($createDatabaseCommand);
            CliHandler::text("Database has been created");

            CliHandler::text("Creating database structure and inserting base data into the tables, please wait");

            shell_exec($runMigrations);
            CliHandler::text("Base data base been inserted into the tables", false);

        }
        CliHandler::infoText("Finished configuring the database.");
    }

    /**
     * This function will install the node packages if this is development mode
     */
    private static function installPackages(){
        if(self::MODE_DEVELOPMENT === self::$userSelectedMode){
            CliHandler::infoText("Started installing node modules.");
            CliHandler::errorText("Yes - You might see some errors here and there, there is nothing I can do about it - it's just the packages dependencies/problems.");
            {
                $npmUpdateCommand     = 'npm update';
                $npmInstallCommand    = 'npm install -g --unsafe-perm';
                $npmSassCommand       = 'npm i node-sass -g --unsafe-perm';
                $npmSassCommand2      = 'nodejs node_modules/node-sass/scripts/install.js';
                $npmSasRebuildCommand = 'npm rebuild node-sass';

                shell_exec($npmUpdateCommand);
                CliHandler::text("Npm update has been finished.");

                shell_exec($npmInstallCommand);
                CliHandler::text("Npm install has been finished.");

                shell_exec($npmSassCommand);
                shell_exec($npmSassCommand2);
                CliHandler::text("Npm sass install has been finished.");

                shell_exec($npmSasRebuildCommand);
                CliHandler::text("Npm sass rebuild has been finished.", false);
            }
            CliHandler::infoText("Finished installing node modules.");
        }
    }

    /**
     *  This function will crate all the required folders
     */
    private static function createFolders(){
        CliHandler::infoText("Started creating folders.");
        {
            $uploadDir       = self::PUBLIC_DIR . DIRECTORY_SEPARATOR . self::UPLOAD_DIR;
            $uploadFilesDir  = self::PUBLIC_DIR . DIRECTORY_SEPARATOR . self::UPLOAD_DIR_FILES;
            $uploadImagesDir = self::PUBLIC_DIR . DIRECTORY_SEPARATOR . self::UPLOAD_DIR_IMAGES;
            $uploadVideosDir = self::PUBLIC_DIR . DIRECTORY_SEPARATOR . self::UPLOAD_DIR_VIDEOS;

            if( !file_exists($uploadDir) ){
                mkdir($uploadDir);
            }
            if( !file_exists($uploadFilesDir) ){
                mkdir($uploadFilesDir);
            }
            if( !file_exists($uploadImagesDir) ){
                mkdir($uploadImagesDir);
            }
            if( !file_exists($uploadVideosDir) ){
                mkdir($uploadVideosDir);
            }
        }
        CliHandler::infoText("Finished creating folders.");
    }

    /**
     * This function will build symfony cache
     */
    private static function buildCache(){
        CliHandler::infoText("Started building cache.");
        {
            $clearCacheCommand  = self::$phpExecutable . " bin/console cache:clear";
            $warmupCacheCommand = self::$phpExecutable . " bin/console cache:warmup";

            shell_exec($clearCacheCommand);
            CliHandler::text("Cache has been cleared.");

            shell_exec($warmupCacheCommand);
            CliHandler::text("Cache has been warmed up.", false);
        }
        CliHandler::infoText("Finished building cache.");
    }

    /**
     * This function will set necessary folders permissions
     */
    private static function setPermissions(){

        CliHandler::infoText("Started setting permissions.");
        {
            $chownCommandVarFolder = "chown -R www-data var";
            $chgrpCommandVarFolder = "chgrp -R www-data var";
            $chmodCommandVarFolder = "chmod -R 777 var";

            $chownCommandVendorFolder = "chown -R www-data vendor";
            $chgrpCommandVendorFolder = "chgrp -R www-data vendor";

            shell_exec($chownCommandVarFolder);
            CliHandler::text("Owner for `var` folder has been set.");

            shell_exec($chgrpCommandVarFolder);
            CliHandler::text("Group for `var` folder has been set.");

            shell_exec($chmodCommandVarFolder);
            CliHandler::text("Read, write, execute permissions for `var` folder have been set.");

            shell_exec($chownCommandVendorFolder);
            CliHandler::text("Owner for `vendor` folder has been set.");

            shell_exec($chgrpCommandVendorFolder);
            CliHandler::text("Group for `vendor` folder has been set.");
        }
        CliHandler::infoText("Finished setting permissions.");

    }

    /**
     * This function will print line that is used to mark beginning and end of the installer
     */
    private static function installerAreaLine(){
        CliHandler::lineSeparator(80, '=', CliHandler::$textBlue);
    }

    /**
     * This function will generate the key used for encrypting passwords
     * @throws Exception
     */
    private static function generateEncryptionKey(){
        CliHandler::infoText("Started generating encryption key.");
        {
            $encryptionKeyGenerationCommand = self::$phpExecutable . ' bin/console --env=dev encrypt:genkey';
            $encryptionKey = trim( shell_exec($encryptionKeyGenerationCommand) );

            YamlFileParserService::replaceArrayNodeValue(self::CONFIG_ENCRYPTION_KEY_ENCRYPT_KEY, $encryptionKey, self::CONFIG_ENCRYPTION_YAML_PATH);
            CliHandler::text($encryptionKey, false);
        }
        CliHandler::infoText("Finished generating and setting the encryption key. Copy the key in safe place!");
    }

    /**
     * Write some additional information on the end
     */
    private static function writeEndInformation(){

        CliHandler::infoText("
        #########################################################################################################################
        #                             Now this are the things that You need to do manually.                                     # 
        #                                                                                                                       #
        #    User register:                                                                                                     #
        #    Simply open the project in browser and if no user is registered, then You will see register button                 #
        #                                                                                                                       #
        #    Local Server                                                                                                       #
        #     Get the script from: https://symfony.com/download                                                                 #
        #    Now You can call in the root of project: symfony server:start --port=8001                                          #
        #    Go to: http://127.0.0.1:8001                                                                                       #
        #    For more information visit: https://volmarg.github.io/ (if still works)                                            #
        #                                                                                                                       #    
        #    For more information about running the encore watcher for development mode - go to:                                #
        #    https://volmarg.github.io/docs/technical/development-mode                                                          #
        #########################################################################################################################
        ");

    }

    /**
     * Check mysql mode
     */
    private static function checkMysqlMode(){
        CliHandler::infoText("Started checking Mysql mode.");
        {
            $modeToDisable = "ONLY_FULL_GROUP_BY";

            $conn   = new \mysqli(self::$mysqlHost, self::$mysqlLogin, self::$mysqlPassword);
            $sql    = "SELECT @@sql_mode";

            $result = $conn->query($sql);
            $row    = mysqli_fetch_assoc($result);

            $modes = reset($row);

            if( !strstr($modeToDisable, $modes) ){
                echo "Seems like Mysql mode is ok";
                CliHandler::newLine();
            }else{
                CliHandler::errorText("Your database mode is incorrect: ". $modes);
                CliHandler::errorText("Check problems section at: https://volmarg.github.io/installation/");
                CliHandler::errorText("Fix that later or the project won't run correctly");
            }

        }
        CliHandler::infoText("Finished checking Mysql mode.");
    }

    /**
     * Will define the php executable to be called
     */
    private static function definePhpExecutable(): void
    {
        $php74 = trim(shell_exec("which " . self::PHP_EXECUTABLE_7_4));
        if( !empty($php74) ){
            self::$phpExecutable = self::PHP_EXECUTABLE_7_4;
        }else{
            self::$phpExecutable = self::PHP_EXECUTABLE_DEFAULT;
        }
    }

}
