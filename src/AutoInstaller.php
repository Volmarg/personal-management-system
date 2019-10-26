<?php

namespace App;

/**
 * This script is run from CLI (composer) but symfony will crash later that no such file is found
 */
if( php_sapi_name() === 'cli' ){
    include_once 'src/Controller/Utils/CliHandler.php';
}

use App\Controller\Utils\CliHandler;
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

    const UPLOAD_DIR        = 'upload';
    const UPLOAD_DIR_IMAGES = 'upload/images';
    const UPLOAD_DIR_FILES  = 'upload/files';

    const APP_SECRET        = 'b9abc19ae10d53eb7cf5b5684ec6511f';

    const ENV_DEV   = "dev";
    const ENV_PROD  = "prod";

    const ENV_KEY_APP_ENV               = 'APP_ENV';
    const ENV_KEY_APP_DEBUG             = 'APP_DEBUG';
    const ENV_KEY_APP_SECRET            = 'APP_SECRET';
    const ENV_KEY_APP_DEMO              = 'APP_DEMO';
    const ENV_KEY_MAILER_URL            = 'MAILER_URL';
    const ENV_KEY_DATABASE_URL          = 'DATABASE_URL';
    const ENV_KEY_UPLOAD_DIR            = 'UPLOAD_DIR';
    const ENV_KEY_IMAGES_UPLOAD_DIR     = 'IMAGES_UPLOAD_DIR';
    const ENV_KEY_FILES_UPLOAD_DIR      = 'FILES_UPLOAD_DIR';

    static $is_node_installed = false;

    static $is_npm_installed = false;

    static $is_mysql_installed = false;

    static $dependencies_state = [];

    static $mysql_port     = '';

    static $mysql_host     = '';

    static $mysql_login    = '';

    static $mysql_password = '';

    static $mysql_database = '';

    static $user_selected_mode = '';

    static $encryption_key = '';
    
    public static function run(){

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

        CliHandler::text("Mysql: "  .  ( self::$is_mysql_installed    ? CliHandler::successMark() : CliHandler::failureMark() ) );
        CliHandler::text("Node: "   .  ( self::$is_node_installed     ? CliHandler::successMark() : CliHandler::failureMark() ) );
        CliHandler::text("Npm: "    .  ( self::$is_npm_installed      ? CliHandler::successMark() : CliHandler::failureMark() ) );

        if( in_array(false, self::$dependencies_state) ){
            CliHandler::errorText(PHP_EOL . "At least one of required packages is not present - please install it first");
        }
    }

    /**
     * This function will get all needed RAW information about dependencies
     */
    private static function getDependenciesInformation(){

        self::$is_node_installed   = trim(shell_exec('node -v'));
        self::$is_npm_installed    = trim(shell_exec('npm -v'));
        self::$is_mysql_installed  = trim(shell_exec('mysql -V'));

        self::$dependencies_state = [
            self::$is_mysql_installed,
            self::$is_node_installed,
            self::$is_npm_installed,
        ];

    }

    /**
     * This function is responsible for interaction with user - getting information from him/her
     */
    private static function askQuestions(){

        while( empty(self::$mysql_port) ){
            self::$mysql_port = CliHandler::getUserInput("What is Your database port?");
        }
        while( empty(self::$mysql_host) ){
            self::$mysql_host = CliHandler::getUserInput("What is Your database host?");
        }
        while( empty(self::$mysql_login) ){
            self::$mysql_login = CliHandler::getUserInput("What is Your database login?");
        }

        self::$mysql_password  = CliHandler::getUserInput("What is Your database password?"); //it can be empty

        while( empty(self::$mysql_database) ){
            self::$mysql_database = CliHandler::getUserInput("How do You want to name Your new database?");
        }

        self::$user_selected_mode = CliHandler::choices([
            self::MODE_DEVELOPMENT,
            self::MODE_PRODUCTION,
        ], "Which mode do You want to install?");

        CliHandler::errorText('!! Beware that if You provided existing database then it will be dropped. !!');

        $selected_option = CliHandler::choices([
            self::YES,
            self::NO,
        ], "Do You want to continue?");

        if( self::NO === $selected_option ){
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
        $env_file_name = '.env';

        $db_login       = self::$mysql_login;
        $db_password    = self::$mysql_password;
        $db_host        = self::$mysql_host;
        $db_port        = self::$mysql_port;
        $db_name        = self::$mysql_database;


        $db_connection_check_command = "mysql -u {$db_login} -h {$db_host} --port={$db_port} -e\"quit\"";

        if( !empty($db_password) ){
            $db_connection_check_command .= " -p{$db_password}";
        }

        exec($db_connection_check_command, $output, $connection_check_result);

        $are_credentail_valid = empty($connection_check_result); //returns nothing if credentials are ok

        if( !$are_credentail_valid ){
            CliHandler::errorText("Database credentials are incorrect, could not connect - aborting.");
            CliHandler::infoText("Command used to check credentials: ". $db_connection_check_command);
            self::installerAreaLine();
            exit();
        }

        if( file_exists($env_file_name) ){
            CliHandler::errorText("Env file already exist so I'm aborting. Copy Your env or remove it and let the installer proceed.");
            self::installerAreaLine();
            exit();
        }

        $database_url = "mysql://{$db_login}:{$db_password}@{$db_host}:{$db_port}/{$db_name}";

        if( self::MODE_DEVELOPMENT === self::$user_selected_mode ){
            $env    = self::ENV_DEV;
            $debug  = "true";
        }else{
            $env    = self::ENV_PROD;
            $debug  = "false";
        }
        $file_handler = fopen($env_file_name, 'w+');
        {
            fwrite($file_handler,self::ENV_KEY_APP_ENV      . "="  . $env              . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_APP_DEBUG    . "="  . $debug            . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_APP_SECRET   . "="  . self::APP_SECRET  . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_MAILER_URL   . "="  . self::MAILER_URL  . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_DATABASE_URL . "="  . $database_url     . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_UPLOAD_DIR   . "="  . self::UPLOAD_DIR  . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_IMAGES_UPLOAD_DIR . "="  . self::UPLOAD_DIR_IMAGES . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_FILES_UPLOAD_DIR  . "="  . self::UPLOAD_DIR_FILES  . PHP_EOL);
        }
        fclose($file_handler);
        CliHandler::infoText('Env file has been created.');
    }

    /**
     * This function will create database
     */
    private function setDatabase(){
        CliHandler::infoText("Started configuring the database.");
        {
            $drop_database_command   = "bin/console doctrine:database:drop -n --force";
            $create_database_command = "bin/console doctrine:database:create -n";
            $build_tables            = "bin/console doctrine:schema:update -n --env=dev --force"; //there is symfony bug so it must be done like this

            shell_exec($drop_database_command);
            CliHandler::text("Database has been dropped (if You provided the existing one)");

            shell_exec($create_database_command);
            CliHandler::text("Database has been created");

            CliHandler::text("Creating database tables, please wait");

            shell_exec($build_tables);
            CliHandler::text("Database tables has been created", false);

        }
        CliHandler::infoText("Finished configuring the database.");
    }

    /**
     * This function will install the node packages if this is development mode
     */
    private function installPackages(){
        if(self::MODE_DEVELOPMENT === self::$user_selected_mode){
            CliHandler::infoText("Started installing node modules.");
            CliHandler::errorText("Yes - You might see some errors here and there, there is nothing I can do about it - it's just the packages dependencies/problems.");
            {
                $npm_update_command         = 'npm update';
                $npm_install_command        = 'npm install -g --unsafe-perm';
                $npm_sass_command           = 'npm i node-sass -g --unsafe-perm';
                $npm_sass_command_2         = 'nodejs node_modules/node-sass/scripts/install.js';
                $npm_sas_rebuild_command    = 'npm rebuild node-sass';

                shell_exec($npm_update_command);
                CliHandler::text("Npm update has been finished.");

                shell_exec($npm_install_command);
                CliHandler::text("Npm install has been finished.");

                shell_exec($npm_sass_command);
                shell_exec($npm_sass_command_2);
                CliHandler::text("Npm sass install has been finished.");

                shell_exec($npm_sas_rebuild_command);
                CliHandler::text("Npm sass rebuild has been finished.", false);
            }
            CliHandler::infoText("Finished installing node modules.");
        }
    }

    /**
     *  This function will crate all the required folders
     */
    private function createFolders(){
        CliHandler::infoText("Started creating folders.");
        {
            $upload_dir         = self::PUBLIC_DIR . DIRECTORY_SEPARATOR . self::UPLOAD_DIR;
            $upload_files_dir   = self::PUBLIC_DIR . DIRECTORY_SEPARATOR . self::UPLOAD_DIR_FILES;
            $upload_images_dir  = self::PUBLIC_DIR . DIRECTORY_SEPARATOR . self::UPLOAD_DIR_IMAGES;

            if( !file_exists($upload_dir) ){
                mkdir($upload_dir);
            }
            if( !file_exists($upload_files_dir) ){
                mkdir($upload_files_dir);
            }
            if( !file_exists($upload_images_dir) ){
                mkdir($upload_images_dir);
            }
        }
        CliHandler::infoText("Finished creating folders.");
    }

    /**
     * This function will build symfony cache
     */
    private function buildCache(){
        CliHandler::infoText("Started building cache.");
        {
            $clear_cache_command    = "bin/console cache:clear";
            $warmup_cache_command   = "bin/console cache:warmup";

            shell_exec($clear_cache_command);
            CliHandler::text("Cache has been cleared.");

            shell_exec($warmup_cache_command);
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
            $chown_command = "chown -R www-data var";
            $chgrp_command = "chgrp -R www-data var";
            $chmod_command = "chmod -R 777 var";

            shell_exec($chown_command);
            CliHandler::text("Owner has been set.");

            shell_exec($chgrp_command);
            CliHandler::text("Group has been set.");

            shell_exec($chmod_command);
            CliHandler::text("Read, write, execute permissions have been set.", false);
        }
        CliHandler::infoText("Finished setting permissions.");

    }

    /**
     * This function will print line that is used to mark beginning and end of the installer
     */
    private static function installerAreaLine(){
        CliHandler::lineSeparator(80, '=', CliHandler::$text_blue);
    }

    /**
     * This function will generate the key used for encrypting passwords
     */
    private static function generateEncryptionKey(){
        CliHandler::infoText("Started generating encryption key.");
        {
            $encryption_key_generation_command = 'bin/console --env=dev encrypt:genkey';
            $encryption_key = trim( shell_exec($encryption_key_generation_command) );
            CliHandler::text($encryption_key, false);
        }
        CliHandler::infoText("Finished generating encryption key.");
    }

    /**
     * Write some additional information on the end
     */
    private static function writeEndInformation(){

        CliHandler::infoText("
        #########################################################################################################################
        #                             Now this are the things that You need to do manually.                                     # 
        #                                                                                                                       #
        #    Password encryption:                                                                                               # 
        #    Open file: ./config/services.yaml                                                                                  # 
        #    Modify paramters section, it should look like this now:                                                            #
        #       locale: 'en'                                                                                                    #
        #       encrypt_key: 'Your key goes here' (without the [OK] part                                                        #
        #    Modify paramters section, it should look like this now:                                                            #
        #                                                                                                                       #
        #    User register:                                                                                                     #
        #    Run this command and follow all steps:                                                                             #
        #    sudo bin/console fos:user:create --super-admin                                                                     #
        #                                                                                                                       #
        #    Now You can start the project: bin/console --env=dev server:run 0.0.0.0:8001 (or other port)                       #
        #    Go to: http://127.0.0.1:8001                                                                                       #
        #    For more information visit: https://volmarg.github.io/ (if still works)                                            #
        #                                                                                                                       #
        #    For more information about running the encore watcher for development mode - go to:                                #
        #    https://volmarg.github.io/developer-mode/                                                                          #
        #########################################################################################################################
        ");

    }

    /**
     * Check mysql mode
     */
    private function checkMysqlMode(){
        CliHandler::infoText("Started checking Mysql mode.");
        {
            $mode_to_disable = "ONLY_FULL_GROUP_BY";

            $conn   = new \mysqli(self::$mysql_host, self::$mysql_login);
            $sql    = "SELECT @@sql_mode";

            $result = $conn->query($sql);
            $row    = mysqli_fetch_assoc($result);

            $modes = reset($row);

            if( !strstr($mode_to_disable, $modes) ){
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
     * TODO:
     *  add checking php version in composer
     */
}
