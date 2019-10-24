<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;


class InstallProjectCommand extends Command{

    const YES = "yes";
    const NO  = "no";

    const MAILER_URL = 'null://localhost';

    const MODE_DEVELOPMENT = 'Development';
    const MODE_PRODUCTION  = 'Production';

    const UPLOAD_DIR        = 'public/upload';
    const UPLOAD_DIR_IMAGES = 'public/upload/images';
    const UPLOAD_DIR_FILES  = 'public/upload/files';

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

    private $failure_mark = "✗";

    private $success_mark = "✓";

    private $php_version = '';

    private $is_node_installed = false;

    private $is_npm_installed = false;

    private $is_composer_installed = false;

    private $is_mysql_installed = false;

    private $mysql_port     = '';

    private $mysql_host     = '';

    private $mysql_login    = '';

    private $mysql_password = '';

    private $mysql_database = '';

    private $user_selected_mode = '';

    private $dependencies_state = [];

    private $encryption_key = '';

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:install:project';

    protected function configure()
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->addStyles($output);

        $this->getDependenciesInformation();
        $this->modifyDependenciesInformation();

        $this->writeGeneralInformation($output, $io);
        $this->writeDependenciesInformation($output, $io);

        $this->askQuestions($io);

        $this->tellUserToWait($output, $io);

        $this->buildEnv($io);
        $this->setDatabase($output, $io);
        $this->installPackages($output, $io);
        $this->createFolders($output, $io);
        $this->buildCache($output, $io);
        $this->setPermissions($output, $io);

        $this->generateEncryptionKey($output, $io);

        $this->writeEndInformation($output, $io);

    }

    private function tellUserToWait(OutputInterface $output, SymfonyStyle $io){
        $io->success("Thank You, the configuration will start in a moment. Please wait. P.s: You can still cancel here. (Ctrl+c)");

        $before_install_progress_barr = new ProgressBar($output, 100);
        $before_install_progress_barr->start(0);
        $i = 0;

        while ($i++ < 100) {
            $before_install_progress_barr->advance();
            usleep(30000);
        }

        $output->writeln("");
        $output->writeln("");
    }
    /**
     * This function is responsible for interaction with user - getting information from him/her
     * @param SymfonyStyle $io
     */
    private function askQuestions(SymfonyStyle $io){

        $do_you_want_to_continue_answer = $io->choice("Do You want to continue?", [
            self::YES,
            self::NO,
        ]);

        if( self::NO === $do_you_want_to_continue_answer ){
            $io->error("Well... ok");
            exit;
        };

        $io->success("I see that You have mysql already installed - please provide me the information below.");
        $io->warning("Provide NEW database name, if the database with this name exist then it will be purged!");

        while( empty($this->mysql_port) ){
            $this->mysql_port = $io->ask("What is Your database port? ");
        }
        while( empty($this->mysql_host) ){
            $this->mysql_host = $io->ask("What is Your database host? ");
        }
        while( empty($this->mysql_login) ){
            $this->mysql_login = $io->ask("What is Your database login? ");
        }
        while( empty($this->mysql_password) ){
            $this->mysql_password = $io->ask("What is Your database password? ");
        }
        while( empty($this->mysql_database) ){
            $this->mysql_database = $io->ask("How do You want to name Your new database? ");
        }

        $this->user_selected_mode = $io->choice("Which mode do You want to install and configure?", [
            self::MODE_DEVELOPMENT,
            self::MODE_PRODUCTION,
        ]);

    }

    /**
     * This function writes basic information like required space on hdd etc
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    private function writeGeneralInformation(OutputInterface $output, SymfonyStyle $io){

        $io->success("This script will install and configure the project - make sure that You run this command as sudo.");
        $io->success("Despite the fact that this installer should handle most of things You will still need to do some things manually on the end.");
        $io->success("Keep in mind that this is a simple one time run script - it won't check if You have already packages installed.");

        $output->writeln("");
        $output->writeln("Basic requirements: ");

        $output->writeln("<text>Internet connection</text>");
        $output->writeln("<text>Up to 500mb free space for production version</text>");
        $output->writeln("<text>Up to 1gb free space for development version</text>");
    }

    /**
     * This function write information about dependencies such as node/npm
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    private function writeDependenciesInformation(OutputInterface $output, SymfonyStyle $io){
        $output->writeln("");
        $output->writeln("Dependencies requirements: ");

        $composer_text = "Is composer installed";
        if($this->is_mysql_installed){
            $output->writeln("<text>{$composer_text}: <bg>{$this->success_mark}</bg></text>");
        }else{
            $output->writeln("<text>{$composer_text}: <rg>{$this->failure_mark}</rg></text>");
        }

        $mysql_text = "Is mysql installed";
        if($this->is_composer_installed){
            $output->writeln("<text>{$mysql_text}: <bg>{$this->success_mark}</bg></text>");
        }else{
            $output->writeln("<text>{$mysql_text}: <rg>{$this->failure_mark}</rg></text>");
        }

        $node_text = "Is node installed";
        if($this->is_node_installed){
            $output->writeln("<text>{$node_text}: <bg>{$this->success_mark}</bg></text>");
        }else{
            $output->writeln("<text>{$node_text}: <rg>{$this->failure_mark}</rg></text>");
        }

        $npm_text = "Is npm installed";
        if($this->is_npm_installed){
            $output->writeln("<text>{$npm_text}: <bg>{$this->success_mark}</bg></text>");
        }else{
            $output->writeln("<text>{$npm_text}: <rg>{$this->failure_mark}</rg></text>");
        }

        $output->writeln("<text>Php version: {$this->php_version} : <bg>{$this->success_mark}</bg></text>");

        if( in_array(false, $this->dependencies_state)){
            $io->error("At least one of the dependencies is not present. Install it first.");
            exit;
        }

    }

    /**
     * This function adds additional styles for coloring in this command
     * @param OutputInterface $output
     */
    private function addStyles( OutputInterface $output){
        $outputStyle = new OutputFormatterStyle('yellow' );
        $output->getFormatter()->setStyle('text', $outputStyle);

        $outputStyle = new OutputFormatterStyle('green', null, ['bold'] );
        $output->getFormatter()->setStyle('bg', $outputStyle);

        $outputStyle = new OutputFormatterStyle('red', null, ['bold'] );
        $output->getFormatter()->setStyle('rg', $outputStyle);
    }

    /**
     * This function will get all needed RAW information about dependencies
     */
    private function getDependenciesInformation(){
        return;

        $node_is_installed_check_process     = new Process(['node', '-v']);
        $npm_is_installed_check_process      = new Process(['npm', '-v']);

        $composer_is_installed_check_process  = new Process(['composer', '-V']);
        $mysql_is_installed_check_process     = new Process(['mysql', '-V']);

        $npm_is_installed_check_process->run();
        $node_is_installed_check_process->run();
        $composer_is_installed_check_process->run();
        $mysql_is_installed_check_process->run();

        $this->is_node_installed = trim($node_is_installed_check_process->getOutput());
        $this->is_npm_installed  = trim($npm_is_installed_check_process->getOutput());
        $this->php_version       = phpversion();

        $this->is_composer_installed = trim($composer_is_installed_check_process->getOutput());
        $this->is_mysql_installed    = trim($mysql_is_installed_check_process->getOutput());
        $this->is_mysql_installed    = trim($mysql_is_installed_check_process->getOutput());

        $this->dependencies_state = [
            $this->is_composer_installed,
            $this->is_mysql_installed,
            $this->is_node_installed,
            $this->is_npm_installed,
        ];
    }

    /**
     * This function edit the fetched dependencies data as sometimes there are to much/few information
     */
    private function modifyDependenciesInformation(){
        preg_match('#^(([0-9])*(\.)*)*([0-9])#', $this->php_version, $php_version_matches);

        $this->is_node_installed = str_replace('v', '', $this->is_node_installed);
        $this->php_version  = $php_version_matches[0];

    }

    /**
     * This function will create database and run the migrations
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    private function setDatabase(OutputInterface $output, SymfonyStyle $io){

        return;
        $io->newLine();
        $io->success("Started configuring the database.");
        {
            $drop_database_process    = new Process(['bin/console doctrine:database:drop', '-n']);
            $create_database_process  = new Process(['bin/console doctrine:database:create', '-n']);
            $run_migrations_process   = new Process(['bin/console doctrine:schema:update', '-n --env=dev --force']);

            $drop_database_process->run();
            $create_database_process->run();
            $run_migrations_process->run();

            $drop_database_result   = trim($drop_database_process->getOutput());
            $io->newLine();
            $output->write($drop_database_result);

            $create_database_result = trim($create_database_process->getOutput());
            $io->newLine();
            $output->write($create_database_result);

            $run_migrations_result  = trim($run_migrations_process->getOutput());
            $io->newLine();
            $output->write($run_migrations_result);
        }
        $io->newLine();
        $io->success("Finished configuring the database.");

    }

    /**
     * This function will install
     *  - composer packages
     *  - npm/node packages
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    private function installPackages(OutputInterface $output, SymfonyStyle $io){

        return;
        $io->newLine();
        $io->success("Started installing packages.");
        {
            $install_composer_packages_process = new Process(['composer install']);

            $install_composer_packages_process->run();
            $install_composer_packages_result = trim($install_node_packages_process->getOutput());

            $io->newLine();
            $output->write($install_composer_packages_result);
        }
        $io->newLine();
        $io->success("Finished installing packages.");

    }

    /**
     * This function will crate all the required folders
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    private function createFolders(OutputInterface $output, SymfonyStyle $io){

        return;
        $io->newLine();
        $io->success("Started creating folders.");
        {
            if( !file_exists(self::UPLOAD_DIR) ){
                mkdir(self::UPLOAD_DIR);
            }
            if( !file_exists(self::UPLOAD_DIR_FILES) ){
                mkdir(self::UPLOAD_DIR_FILES);
            }
            if( !file_exists(self::UPLOAD_DIR_IMAGES) ){
                mkdir(self::UPLOAD_DIR_IMAGES);
            }
        }
        $io->newLine();
        $io->success("Finished creating folders.");
    }

    /**
     * This function prepares the cache for initial project usage
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    private function buildCache(OutputInterface $output, SymfonyStyle $io){
        return;
        $io->newLine();
        $io->success("Started building cache.");
        {
            $clear_cache_process    = new Process(['bin/console cache:clear']);
            $clear_cache_process->run();
            $clear_cache_result     = trim($clear_cache_process->getOutput());

            $io->newLine();
            $output->write($clear_cache_result);

            $warmup_cache_process   = new Process(['bin/console cache:warmup']);
            $warmup_cache_result    = trim($warmup_cache_process->getOutput());
            $warmup_cache_process->run();

            $io->newLine();
            $output->write($warmup_cache_result);
        }
        $io->newLine();
        $io->success("Finished building cache.");
    }

    /**
     * This function will set the permissions for certain folders
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    private function setPermissions(OutputInterface $output, SymfonyStyle $io){
        return;
        $io->newLine();
        $io->success("Started setting permissions.");
        {
            $chown_process = new Process(['chown -R www-data var']);
            $chown_process->run();
            $chown_result = trim($chown_process->getOutput());

            $io->newLine();
            $output->write($chown_result);

            $chgrp_process = new Process(['chgrp -R www-data var']);
            $chgrp_process->run();
            $chgrp_result = trim($chgrp_process->getOutput());

            $io->newLine();
            $output->write($chgrp_result);

            $chmod_process = new Process(['chmod -R 777 var']);
            $chmod_process->run();
            $chmod_result = trim($chmod_process->getOutput());

            $io->newLine();
            $output->write($chmod_result);
        }
        $io->newLine();
        $io->success("Finished setting permissions.");
    }

    /**
     * This function will generate the key used for encrypting passwords
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    private function generateEncryptionKey(OutputInterface $output, SymfonyStyle $io){
        return;
        $io->newLine();
        $io->success("Started generating encryption key.");
        {
            $encryption_key_generate_process = new Process(['bin/console --env=dev encrypt:genkey']);
            $encryption_key_generate_process->run();
            $this->encryption_key = trim($encryption_key_generate_process->getOutput());

            $io->newLine();
            $output->write($this->encryption_key);
        }
        $io->newLine();
        $io->success("Finished generating encryption key.");
    }

    /**
     * This function writes additional information on the end
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     */
    private function writeEndInformation(OutputInterface $output, SymfonyStyle $io){
        $io->warning("Now this are the things that You need to do manually now.");
        $io->warning("Password encryption:");
        $output->writeln("<text>Open file: ./config/services.yaml </text>");
        $output->writeln("<text>Modify paramters section, it should look like this now: </text>");

        $output->writeln("<text>parameters</text>");
        $output->writeln("<text   locale: 'en'</text>");
        $output->writeln("<text>  encrypt_key: 'yJouvLW2cs-jfzMg5Mg52FiRU1YPBSjflcMgQTKqCt8r'</text>");

        $io->warning("User register:");
        $output->writeln("<text>Run this command and follow all steps:</text>");
        $output->writeln("<text>sudo bin/console fos:user:create --super-admin</text>");


        $io->success("Now You can start the project: bin/console --env=dev server:run 0.0.0.0:8001 (or other port)");
        $io->success("Go to: http://127.0.0.1:8001");
        $io->warning("For more information visit: https://volmarg.github.io/ (if still works)");

        if( self::MODE_DEVELOPMENT === $this->user_selected_mode ){
            $io->error("Since You decided to use development mode - please follow also this steps: https://volmarg.github.io/developer-mode/");
            $io->warning("I decided NOT to run this commands in installation scripts as node can cause some problems so it's better to do it alone.");
            $io->success("Well. You decided to use developer mode so You know how to run the commands and use my tips :D.");
        }
    }

    /**
     * This function will build the basic version of env
     * @param SymfonyStyle $io
     */
    private function buildEnv(SymfonyStyle $io){

        return;

        $io->success("Started building env file.");

        if( file_exists('.env') ){
            $io->warning('Env file already exist so I am not modifying it - check later if everything is there in it.');
            return;
        }

        $database_url = "mysql://{$this->mysql_login}:{$this->mysql_password}@{$this->mysql_host}:{$this->mysql_port}/{$this->mysql_database}";

        if( self::MODE_DEVELOPMENT === $this->user_selected_mode ){
            $env    = self::ENV_DEV;
            $debug  = true;
        }else{
            $env    = self::ENV_PROD;
            $debug  = false;
        }

        $file_handler = fopen('.env', 'w+');
        {
            fwrite($file_handler,self::ENV_KEY_APP_ENV      . "="  . $env              . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_APP_DEBUG    . "="  . $debug            . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_APP_SECRET   . "="  . self::APP_SECRET  . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_MAILER_URL   . "="  . self::MAILER_URL  . PHP_EOL);
            fwrite($file_handler,self::ENV_KEY_DATABASE_URL . "="  . $database_url     . PHP_EOL);
        }
        fclose($file_handler);

        $io->newLine();
        $io->success('Env file has been created.');

    }

    private function todo(){

        /**
         * TODO: check if minimum versions are there and if packages are installed
         *  allow to pick dev or prod
         *  allow to enter db name and mysql access credentials
         *  allow to enter user name and password
         *  are mysql settings correct
         *  is the xml php library there
         *  are folders privileges set?
         *  create .env based on all the choices
         *  check if the database credentials provided by user are correct.
         */

    }

}