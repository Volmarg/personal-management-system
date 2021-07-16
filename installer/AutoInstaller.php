<?php

namespace Installer;

/**
 * This script is run from CLI (composer) but symfony will crash later that no such file is found
 * The same goes for the testCheck - this will require later to reimplementing check Env::isTest();
 */
if( php_sapi_name() === 'cli' ){

    if( !file_exists("vendor/autoload.php") ){
        throw new \Exception("Composer autoload file was not found, did You called `composer install` earlier?");
    }

    // order of the inclusions is important!
    include_once 'Services/CliHandlerService.php';
    include_once 'Services/Shell/ShellAbstractService.php';
    include_once 'Services/Shell/ShellBinConsoleService.php';
    include_once 'Services/Shell/ShellPhpService.php';
    include_once 'Services/Shell/ShellMysqlService.php';
    include_once 'Controller/InstallerController.php';
    include_once 'vendor/autoload.php'; // this works properly because installer changes the working directory to root dir
    include_once 'src/Services/Files/Parser/YamlFileParserService.php'; // this works properly because installer changes the working directory to root dir
    include_once 'Services/EnvBuilder.php';
}

use Installer\Controller\Installer\InstallerController;
use Installer\Controller\Utils\CliHandlerService;
use Installer\Services\Shell\EnvBuilder;
use Installer\Services\Shell\ShellBinConsoleService;
use Installer\Services\Shell\ShellMysqlService;
use Installer\Services\Shell\ShellPhpService;
use Exception;

/**
 * Namespace is omitted here as this is only used by composer
 * The coloring methods are here on purpose, can be extracted if needed, this in other cases Symfony has built in coloring
 * Class AutoInstaller
 */
class AutoInstaller {

    const YES = "Yes";
    const NO  = "No";

    const MODE_DEVELOPMENT = 'Development';
    const MODE_PRODUCTION  = 'Production';

    const PUBLIC_DIR = 'public';

    const UPLOAD_DIR        = 'upload';
    const UPLOAD_DIR_IMAGES = 'upload/images';
    const UPLOAD_DIR_FILES  = 'upload/files';
    const UPLOAD_DIR_VIDEOS = 'upload/videos';

    const ENV_DEV  = "dev";
    const ENV_PROD = "prod";

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

    /**
     * @throws Exception
     */
    public static function runDocker(){

        self::definePhpExecutable();
        CliHandlerService::initialize();

        CliHandlerService::newLine();
        self::installerAreaLine();
        {
            self::getDependenciesInformation();

            self::writeGeneralInformation();

            CliHandlerService::lineSeparator();
            CliHandlerService::newLine();

            CliHandlerService::lineSeparator();
            self::setDatabase();

            CliHandlerService::lineSeparator();
            self::createFolders();

            CliHandlerService::lineSeparator();
            self::buildCache();

            CliHandlerService::lineSeparator();
            self::setPermissions();

            CliHandlerService::lineSeparator();
            self::generateEncryptionKey();

            self::writeEndInformation();
        }
        self::installerAreaLine();
        CliHandlerService::newLine();
    }

    /**
     * @throws Exception
     */
    public static function run(){

        self::definePhpExecutable();
        CliHandlerService::initialize();

        CliHandlerService::newLine();
        self::installerAreaLine();
        {
            self::getDependenciesInformation();

            self::writeGeneralInformation();
            self::writeDependenciesMessage();

            CliHandlerService::lineSeparator();
            CliHandlerService::newLine();

            self::askQuestions();

            CliHandlerService::lineSeparator();
            self::checkMysqlMode();

            CliHandlerService::lineSeparator();
            self::buildEnv();

            CliHandlerService::lineSeparator();
            self::setDatabase();

            CliHandlerService::lineSeparator();
            self::createFolders();

            CliHandlerService::lineSeparator();
            self::buildCache();

            CliHandlerService::lineSeparator();
            self::setPermissions();

            CliHandlerService::lineSeparator();
            self::installPackages();

            CliHandlerService::lineSeparator();
            self::generateEncryptionKey();

            self::writeEndInformation();
        }
        self::installerAreaLine();
        CliHandlerService::newLine();
    }

    /**
     * This function writes basic information like required space on hdd etc
     */
    private static function writeGeneralInformation(){

        CliHandlerService::infoText("
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


        CliHandlerService::infoText("Basic requirements:" );
        CliHandlerService::lineSeparator();

        CliHandlerService::text("Internet connection" );
        CliHandlerService::text("Up to 500mb free space for production version" );
        CliHandlerService::text("Up to 1gb free space for development version" );

    }

    /**
     * This function write information about dependencies such as node/npm
     */
    private static function writeDependenciesMessage(){

        CliHandlerService::infoText("To install this project You need to have packages below installed");
        CliHandlerService::lineSeparator();

        CliHandlerService::text("Mysql: "  .  ( self::$isMysqlInstalled    ? CliHandlerService::successMark() : CliHandlerService::failureMark() ) );
        CliHandlerService::text("Node: "   .  ( self::$isNodeInstalled     ? CliHandlerService::successMark() : CliHandlerService::failureMark() ) );
        CliHandlerService::text("Npm: "    .  ( self::$isNpmInstalled      ? CliHandlerService::successMark() : CliHandlerService::failureMark() ) );

        if( in_array(false, self::$dependenciesState) ){
            CliHandlerService::errorText(PHP_EOL . "At least one of required packages is not present - please install it first");
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
            self::$mysqlPort = CliHandlerService::getUserInput("What is Your database port?");
        }
        while( empty(self::$mysqlHost) ){
            self::$mysqlHost = CliHandlerService::getUserInput("What is Your database host?");
        }
        while( empty(self::$mysqlLogin) ){
            self::$mysqlLogin = CliHandlerService::getUserInput("What is Your database login?");
        }

        self::$mysqlPassword  = CliHandlerService::getUserInput("What is Your database password?"); //it can be empty

        while( empty(self::$mysqlDatabase) ){
            self::$mysqlDatabase = CliHandlerService::getUserInput("How do You want to name Your new database?");
        }

        self::$userSelectedMode = CliHandlerService::choices([
            self::MODE_DEVELOPMENT,
            self::MODE_PRODUCTION,
        ], "Which mode do You want to install?");

        CliHandlerService::errorText('!! Beware that if You provided existing database then it will be dropped. !!');

        $selectedOption = CliHandlerService::choices([
            self::YES,
            self::NO,
        ], "Do You want to continue?");

        if( self::NO === $selectedOption ){
            CliHandlerService::errorText("Well... ok");
            self::installerAreaLine();
            exit;
        };
    }

    /**
     * This function will build the basic version of env
     */
    private static function buildEnv(){
        CliHandlerService::infoText("Started building env file.");

        $areCredentialsValid = ShellMysqlService::isDbAccessValid(self::$mysqlLogin, self::$mysqlHost, self::$mysqlPort, self::$mysqlPassword);
        if( !$areCredentialsValid ){
            CliHandlerService::errorText("Database credentials are incorrect, could not connect - aborting.");
            self::installerAreaLine();
            exit();
        }

        if( file_exists(EnvBuilder::ENV_FILE_NAME) ){
            CliHandlerService::errorText("
                Env file already exist so It won't be modified - You must do this manually then. 
                Keep in mind that if the database connection is incorrect - this installer will then crash with PDO errors.
                That's because of how the migrations / doctrine commands works like. 
                Using database configuration from .env
                Please wait...
            ");

            sleep(2);
            self::installerAreaLine();
            return;
        }

        if( self::MODE_DEVELOPMENT === self::$userSelectedMode ){
            $env    = self::ENV_DEV;
            $debug  = "true";
        }else{
            $env    = self::ENV_PROD;
            $debug  = "false";
        }

        EnvBuilder::buildEnv(self::$mysqlLogin, self::$mysqlPassword, self::$mysqlHost, self::$mysqlPort, self::$mysqlDatabase, $env, $debug);
        InstallerController::setEnvKeyAppIsInstalled();
        CliHandlerService::infoText('Env file has been created.');
    }

    /**
     * This function will create database
     * @throws Exception
     */
    private static function setDatabase(){
        CliHandlerService::infoText("Started configuring the database.");
        {
            ShellBinConsoleService::dropDatabase();
            CliHandlerService::text("Database has been dropped (if You provided the existing one)");

            ShellBinConsoleService::createDatabase();
            CliHandlerService::text("Database has been created");

            CliHandlerService::text("Creating database structure and inserting base data into the tables, please wait");

            ShellBinConsoleService::executeMigrations();
            CliHandlerService::text("Base data base been inserted into the tables", false);

        }
        CliHandlerService::infoText("Finished configuring the database.");
    }

    /**
     * This function will install the node packages if this is development mode
     */
    private static function installPackages(){
        if(self::MODE_DEVELOPMENT === self::$userSelectedMode){
            CliHandlerService::infoText("Started installing node modules.");
            CliHandlerService::errorText("Yes - You might see some errors here and there, there is nothing I can do about it - it's just the packages dependencies/problems.");
            {
                $npmUpdateCommand     = 'npm update';
                $npmInstallCommand    = 'npm install -g --unsafe-perm';
                $npmSassCommand       = 'npm i node-sass -g --unsafe-perm';
                $npmSassCommand2      = 'nodejs node_modules/node-sass/scripts/install.js';
                $npmSasRebuildCommand = 'npm rebuild node-sass';

                shell_exec($npmUpdateCommand);
                CliHandlerService::text("Npm update has been finished.");

                shell_exec($npmInstallCommand);
                CliHandlerService::text("Npm install has been finished.");

                shell_exec($npmSassCommand);
                shell_exec($npmSassCommand2);
                CliHandlerService::text("Npm sass install has been finished.");

                shell_exec($npmSasRebuildCommand);
                CliHandlerService::text("Npm sass rebuild has been finished.", false);
            }
            CliHandlerService::infoText("Finished installing node modules.");
        }
    }

    /**
     *  This function will crate all the required folders
     */
    private static function createFolders(){
        CliHandlerService::infoText("Started creating folders.");
        {
            InstallerController::createFolders();
        }
        CliHandlerService::infoText("Finished creating folders.");
    }

    /**
     * This function will build symfony cache
     * @throws Exception
     */
    private static function buildCache(){
        CliHandlerService::infoText("Started building cache.");
        {
            ShellBinConsoleService::clearCache();
            ShellBinConsoleService::buildCache();
        }
        CliHandlerService::infoText("Finished building cache.");
    }

    /**
     * This function will set necessary folders permissions
     */
    private static function setPermissions(){

        CliHandlerService::infoText("Started setting permissions.");
        {
            $chownCommandVarFolder = "chown -R www-data var";
            $chgrpCommandVarFolder = "chgrp -R www-data var";
            $chmodCommandVarFolder = "chmod -R 777 var";

            $chownCommandVendorFolder = "chown -R www-data vendor";
            $chgrpCommandVendorFolder = "chgrp -R www-data vendor";

            shell_exec($chownCommandVarFolder);
            CliHandlerService::text("Owner for `var` folder has been set.");

            shell_exec($chgrpCommandVarFolder);
            CliHandlerService::text("Group for `var` folder has been set.");

            shell_exec($chmodCommandVarFolder);
            CliHandlerService::text("Read, write, execute permissions for `var` folder have been set.");

            shell_exec($chownCommandVendorFolder);
            CliHandlerService::text("Owner for `vendor` folder has been set.");

            shell_exec($chgrpCommandVendorFolder);
            CliHandlerService::text("Group for `vendor` folder has been set.");
        }
        CliHandlerService::infoText("Finished setting permissions.");

    }

    /**
     * This function will print line that is used to mark beginning and end of the installer
     */
    private static function installerAreaLine(){
        CliHandlerService::lineSeparator(80, '=', CliHandlerService::$textBlue);
    }

    /**
     * This function will generate the key used for encrypting passwords
     * @throws Exception
     */
    private static function generateEncryptionKey(){
        CliHandlerService::infoText("Started generating encryption key.");
        {
            $encryptionKey = ShellBinConsoleService::generateEncryptionKey();
            InstallerController::setEncryptionKey($encryptionKey);
            CliHandlerService::text($encryptionKey, false);
        }
        CliHandlerService::infoText("Finished generating and setting the encryption key. Copy the key in safe place!");
    }

    /**
     * Write some additional information on the end
     */
    private static function writeEndInformation(){

        CliHandlerService::infoText("
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
        CliHandlerService::infoText("Started checking Mysql mode.");
        {
           $isModeDisabled = ShellMysqlService::isOnlyFullGroupByMysqlModeDisabled(self::$mysqlLogin, self::$mysqlHost, self::$mysqlPort, self::$mysqlPassword);
            if( $isModeDisabled ){
                echo "Mysql mode is ok";
                CliHandlerService::newLine();
            }else{
                CliHandlerService::errorText("Your database mode is incorrect!");
                CliHandlerService::errorText("Check problems section at: https://volmarg.github.io/installation/");
                CliHandlerService::errorText("Fix that later or the project won't run correctly");
            }

        }
        CliHandlerService::infoText("Finished checking Mysql mode.");
    }

    /**
     * Will define the php executable to be called
     * @throws Exception
     */
    private static function definePhpExecutable(): void
    {
        self::$phpExecutable = ShellPhpService::getExecutableBinaryName();
    }

}
