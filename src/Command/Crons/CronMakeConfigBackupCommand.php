<?php

namespace App\Command\Crons;

use App\Controller\Core\Application;
use App\Services\Files\Archivizer\ZipArchivizer;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TypeError;

/**
 * This command handles creating backup of configuration files
 * - separated from @see CronMakeBackupCommand to make it run more often
 *
 * Class CronMakeConfigBackupCommand
 * @package App\Command
 */
class CronMakeConfigBackupCommand extends Command
{
    const MINIMUM_ARCHIVE_SIZE_IN_BYTES= 6000; // bytes ~6 kilobytes

    const ARGUMENT_BACKUP_DIRECTORY      = 'backup-directory';
    const ARGUMENT_BACKUP_FILE_FILENAME  = "backup-file-name";


    const ENV_FILE_PATH             = ".env";
    const CONFIGURATION_FOLDER_PATH = "./config";

    const ALL_FOLDERS_TO_BACKUP = [
        self::CONFIGURATION_FOLDER_PATH,
    ];

    const ALL_FILES_TO_BACKUP = [
        self::ENV_FILE_PATH
    ];

    protected static $defaultName = 'cron:make-config-backup';

    /**
     * @var ZipArchivizer $archivizer
     */
    private ZipArchivizer $archivizer;

    /**
     * @var SymfonyStyle $io
     */
    private SymfonyStyle $io;

    /**
     * @var string $backupFileName
     */
    private string $backupFileName;

    /**
     * @var Application $app
     */
    private Application $app;

    public function __construct(ZipArchivizer $archivizer, Application $app, string $name = null) {
        parent::__construct($name);

        $this->app        = $app;
        $this->archivizer = $archivizer;
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows to make backup of config files, must be called as sudo to ensure directories creating. ')
            ->addArgument(self::ARGUMENT_BACKUP_DIRECTORY, InputArgument::REQUIRED,'Given directory will be used to store the backups (absolute path, ended with slash)')
            ->addArgument(self::ARGUMENT_BACKUP_FILE_FILENAME, InputArgument::REQUIRED,'Database backup will be saved under that file name')
            ->addUsage("
                sudo php7.4 bin/console cron:make-config-backup /backups/pms config_files_backups (will create a config file backup in the `/backups/pms`)
            ")
            ->setHelp("bin/console cron:make-config-backup /backupDir fileName");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        if( !is_string($input->getArgument(self::ARGUMENT_BACKUP_FILE_FILENAME)) ){
            throw new Exception("Expected backup filename to be a string! Got: " . gettype(is_string($input->getArgument(self::ARGUMENT_BACKUP_FILE_FILENAME))));
        }
        $this->backupFileName = $input->getArgument(self::ARGUMENT_BACKUP_FILE_FILENAME);
        $backupDirectory      = $input->getArgument(self::ARGUMENT_BACKUP_DIRECTORY);

        $this->archivizer->setTargetDirectory($backupDirectory);
        $this->archivizer->setArchiveRecursively(true);

        if( !file_exists($backupDirectory) ){
            mkdir($backupDirectory, 0777, true);
        }

        // the directory still doesn't exists after attempt of crating such
        if( !file_exists($backupDirectory) ){
            throw new Exception("Target backup directory does not exist, even after attempt to create it");
        }

        if( !is_writable($backupDirectory) ){
            throw new Exception("Folder does exist but it's not writable");
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->warning("This command must be called from the root of the project. It won't work from other locations!");
        $io->note("Started config backup process");
        {
            try{
                $this->backupFiles();
            }catch(Exception | TypeError $e){
                $this->app->logExceptionWasThrown($e, [
                    "info" => "Could not backup the config files",
                ]);
                return self::FAILURE;
            }
        }
        $io->note("Config backup process has been completed");

        return self::SUCCESS;
    }

    /**
     * This function creates zip archive for config files
     */
    private function backupFiles(){

        $absolutePathsOfFilesToBackup = array_map(
            fn(string $pathOfFile) => getcwd() . DIRECTORY_SEPARATOR . $pathOfFile,
            self::ALL_FILES_TO_BACKUP
        );

        $this->archivizer->setArchiveName($this->backupFileName);
        $this->archivizer->setDirectoriesToArchive(self::ALL_FOLDERS_TO_BACKUP);
        $this->archivizer->setFilesToArchive($absolutePathsOfFilesToBackup);
        $this->archivizer->setMinimalArchiveSize(self::MINIMUM_ARCHIVE_SIZE_IN_BYTES);
        $this->archivizer->handleArchivizing();

        $message = $this->archivizer->getArchivingStatus();
        if( $this->archivizer->isArchivedSuccessfully() ){
            $this->io->success($message);
        }else{
            $this->io->warning($message);
        }
    }
}
