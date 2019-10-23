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

    const UPLOAD_DIR        = 'upload';
    const UPLOAD_DIR_IMAGES = 'upload/images';
    const UPLOAD_DIR_FILES  = 'upload/files';

    const APP_SECRET        = 'b9abc19ae10d53eb7cf5b5684ec6511f';

    const ENV_DEV   = "dev";
    const ENV_PROD  = "prod";

    private $failure_mark = "✗";

    private $success_mark = "✓";

    private $php_version = '';

    private $is_node_installed = false;

    private $is_npm_installed = false;

    private $is_composer_installed = false;

    private $is_mysql_installed = false;

    private $mysql_port     = '';

    private $mysql_login    = '';

    private $mysql_password = '';

    private $mysql_database = '';

    private $user_selected_mode = '';

    private $dependencies_state = [];

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
        #$this->setDatabase();
        #$this->installPackages();
        #$this->setPermissions();
        #$this->createFolders();

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

        $io->success("Started installing packages.");
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
     * This function will build the basic version of env
     * @param SymfonyStyle $io
     */
    private function buildEnv(SymfonyStyle $io){

        if( file_exists('.env') ){
            $io->warning('Env file already exist so I am not modifying it - check later if everything is there in it.');
            return;
        }

        if( self::MODE_DEVELOPMENT === $this->user_selected_mode ){
            $env    = self::ENV_DEV;
            $debug  = true;
        }else{
            $env    = self::ENV_PROD;
            $debug  = false;
        }

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