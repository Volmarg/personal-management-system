<?php

namespace App\Command\Crons;

use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Modules\Images\MyImagesController;
use App\Controller\Core\Env;
use App\Controller\Modules\ModulesController;
use App\Services\Database\DatabaseExporter;
use App\Services\Files\Archivizer\ZipArchivizer;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This command handles creating backup of databases and uploaded files
 *
 * Class CronMakeBackupCommand
 * @package App\Command
 */
class CronMakeBackupCommand extends Command
{
    const BACKUP_TYPE_SQL   = 'sql';
    const BACKUP_TYPE_FILES = 'files';

    const OPTION_SKIP_FILES                 = 'skip-files';
    const OPTION_SKIP_DATABASE              = 'skip-database';

    const ARGUMENT_BACKUP_DIRECTORY_MODULE  = 'backup-directory';
    const ARGUMENT_BACKUP_DATABASE_FILENAME = "backup-database-name";
    const ARGUMENT_BACKUP_FILES_FILENAME    = "backup-files-name";

    const PUBLIC_DIR_ROOT               = DOT . DIRECTORY_SEPARATOR . 'public';

    const ALL_BACKUPS_TYPES = [
        self::BACKUP_TYPE_FILES,
        self::BACKUP_TYPE_SQL,
    ];

    protected static $defaultName = 'cron:make-backup';

    /**
     * @var DatabaseExporter $databaseExporter
     */
    private $databaseExporter;

    /**
     * @var ZipArchivizer $archivizer
     */
    private ZipArchivizer $archivizer;

    public function __construct(DatabaseExporter $databaseExporter, ZipArchivizer $archivizer, string $name = null) {
        parent::__construct($name);
        $this->databaseExporter = $databaseExporter;
        $this->archivizer       = $archivizer;
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows to make backup of files for given upload modules and database, must be called as sudo to ensure directories creating. ')
            ->addArgument(self::ARGUMENT_BACKUP_DIRECTORY_MODULE, InputArgument::REQUIRED,'Given directory will be used to store the backups (absolute path, ended with slash)')
            ->addArgument(self::ARGUMENT_BACKUP_DATABASE_FILENAME, InputArgument::REQUIRED,'Database backup will be saved under that file name')
            ->addArgument(self::ARGUMENT_BACKUP_FILES_FILENAME, InputArgument::REQUIRED,'Files backup will be saved under that file name')
            ->addOption(self::OPTION_SKIP_FILES, null,InputOption::VALUE_NONE, 'If set - will skip backing up the upload directory.')
            ->addOption(self::OPTION_SKIP_DATABASE, null,InputOption::VALUE_NONE, 'If set - will skip backing up the database.')
            ->addUsage("
                sudo php7.4 bin/console cron:make-backup /var/www/tests/pms sql_backup_file_name files_backup_file_name (will create a backups in the `/var/www/tests/pms`)
            ")
            ->addUsage("
                sudo php7.4 bin/console cron:make-backup /var/www/tests/pms sql_backup_file_name files_backup_file_name --skip-files (will skip the uploaded files in backup process)
            ")
            ->addUsage("
                sudo php7.4 bin/console cron:make-backup /var/www/tests/pms sql_backup_file_name files_backup_file_name --skip-database (will skip the database in backup process)
            ")
            ->setHelp("bin/console cron:make-backup /backupDir databaseName filesName");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    function initialize(InputInterface $input, OutputInterface $output)
    {
        $argumentBackupDirectory = $input->getArgument(self::ARGUMENT_BACKUP_DIRECTORY_MODULE);

        $this->databaseExporter->setBackupDirectory($argumentBackupDirectory);
        $this->archivizer->setTargetDirectory($argumentBackupDirectory);
        $this->archivizer->setArchiveRecursively(true);


        if( !file_exists($argumentBackupDirectory) ){
            mkdir($argumentBackupDirectory, 0777, true);
        }

        // the directory still doesn't exists after attempt of crating such
        if( !file_exists($argumentBackupDirectory) ){
            throw new Exception("Target backup directory does not exist, even after attempt to create it");
        }

        if( !is_writable($argumentBackupDirectory) ){
            throw new Exception("Folder does exist but it's not writable");
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->warning("This command must be called from the root of the project. It won't work from other locations!");
        $io->note("Started backup process");
        {
            $argumentBackupDatabaseFilename  = $input->getArgument(self::ARGUMENT_BACKUP_DATABASE_FILENAME);
            $argumentBackupFilesFilename     = $input->getArgument(self::ARGUMENT_BACKUP_FILES_FILENAME);
            $optionSkipFiles                 = $input->getOption(self::OPTION_SKIP_FILES);
            $optionSkipDatabase              = $input->getOption(self::OPTION_SKIP_DATABASE);

            if ($optionSkipFiles) {
                $io->note(sprintf("Files backup will be skipped"));
            }
            if ($optionSkipDatabase) {
                $io->note(sprintf("Database backup will be skipped"));
            }

            if( !$optionSkipFiles ){
                $io->note("Now making files backup");
                $this->backupFiles($io, $argumentBackupFilesFilename);

                if( !$this->archivizer->isArchivedSuccessfully() ){
                    $io->warning("Finished creating files backup but the archivizing process resulted in failure! Status: {$this->archivizer->getArchivingStatus()}");
                }
            }

            if( !$optionSkipDatabase ){
                $io->note("Now making database backup");
                $this->backupDatabase($io, $argumentBackupDatabaseFilename);
                if( !$this->archivizer->isArchivedSuccessfully() ){
                    $io->warning("Finished creating sql backup but the archivizing process resulted in failure! Status: {$this->archivizer->getArchivingStatus()}");
                }
            }

        }
        $io->note("Backup process has been completed");

        return 1;
    }

    /**
     * This function creates database dump
     * @param SymfonyStyle $io
     * @param string $backupDatabaseFilename
     */
    private function backupDatabase(SymfonyStyle $io, string $backupDatabaseFilename ){
        $this->databaseExporter->setFileName($backupDatabaseFilename);
        $this->databaseExporter->runInternalDatabaseExport();
        $exportMessage = $this->databaseExporter->getExportMessage();

        if( !$this->databaseExporter->isExportedSuccessfully() ){
            $io->warning($exportMessage);
            return;
        }

        $sqlBackupFilename = $this->databaseExporter->getFileName() . $this->databaseExporter->getDumpExtension();
        $sqlBackupPath     = $this->databaseExporter->getDumpedArchiveAbsolutePath();

        $this->archivizer->setArchiveName($sqlBackupFilename);
        $this->archivizer->setDirectoriesToArchive([]);
        $this->archivizer->setFilesToArchive([$sqlBackupPath]);
        $this->archivizer->handleArchivizing();

        $message = $this->archivizer->getArchivingStatus();
        if( $this->archivizer->isArchivedSuccessfully() ){
            $io->success($message);
            unlink($sqlBackupPath);
        }else{
            $io->warning($message);
        }

    }

    /**
     * This function creates zip archive
     * @param SymfonyStyle $io
     * @param string $backupFilesFilename
     */
    private function backupFiles(SymfonyStyle $io, string $backupFilesFilename){

        $uploadDirsForModules = [
          MyImagesController::MODULE_NAME       => self::PUBLIC_DIR_ROOT . DIRECTORY_SEPARATOR . Env::getImagesUploadDir(),
          MyFilesController::MODULE_NAME        => self::PUBLIC_DIR_ROOT . DIRECTORY_SEPARATOR . Env::getFilesUploadDir(),
          ModulesController::MODULE_NAME_VIDEO  => self::PUBLIC_DIR_ROOT . DIRECTORY_SEPARATOR . Env::getVideoUploadDir(),
        ];

        $this->archivizer->setArchiveName($backupFilesFilename);
        $this->archivizer->setDirectoriesToArchive($uploadDirsForModules);
        $this->archivizer->handleArchivizing();

        $message = $this->archivizer->getArchivingStatus();
        if( $this->archivizer->isArchivedSuccessfully() ){
            $io->success($message);
        }else{
            $io->warning($message);
        }
    }
}
