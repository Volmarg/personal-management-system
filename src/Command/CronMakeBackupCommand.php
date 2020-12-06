<?php

namespace App\Command;

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
     * @var DatabaseExporter $database_exporter
     */
    private $database_exporter;

    /**
     * @var ZipArchivizer $archivizer
     */
    private ZipArchivizer $archivizer;

    public function __construct(DatabaseExporter $database_exporter, ZipArchivizer $archivizer, string $name = null) {
        parent::__construct($name);
        $this->database_exporter = $database_exporter;
        $this->archivizer        = $archivizer;
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
        $argument_backup_directory = $input->getArgument(self::ARGUMENT_BACKUP_DIRECTORY_MODULE);

        $this->database_exporter->setBackupDirectory($argument_backup_directory);
        $this->archivizer->setTargetDirectory($argument_backup_directory);
        $this->archivizer->setArchiveRecursively(true);


        if( !file_exists($argument_backup_directory) ){
            mkdir($argument_backup_directory, 0777, true);
        }

        // the directory still doesn't exists after attempt of crating such
        if( !file_exists($argument_backup_directory) ){
            throw new Exception("Target backup directory does not exist, even after attempt to create it");
        }

        if( !is_writable($argument_backup_directory) ){
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
        $io->note("Started backup process");
        {
            $argument_backup_database_filename  = $input->getArgument(self::ARGUMENT_BACKUP_DATABASE_FILENAME);
            $argument_backup_files_filename     = $input->getArgument(self::ARGUMENT_BACKUP_FILES_FILENAME);
            $option_skip_files                  = $input->getOption(self::OPTION_SKIP_FILES);
            $option_skip_database               = $input->getOption(self::OPTION_SKIP_DATABASE);

            if ($option_skip_files) {
                $io->note(sprintf("Files backup will be skipped"));
            }
            if ($option_skip_database) {
                $io->note(sprintf("Database backup will be skipped"));
            }

            if( !$option_skip_files ){
                $this->backupFiles($io, $argument_backup_files_filename);

                if( !$this->archivizer->isArchivedSuccessfully() ){
                    $io->warning("Finished creating files backup but the archivizing process resulted in failure! Status: {$this->archivizer->getArchivingStatus()}");
                }
            }

            if( !$option_skip_database ){
                $this->backupDatabase($io, $argument_backup_database_filename);
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
     * @param string $backup_database_filename
     */
    private function backupDatabase(SymfonyStyle $io, string $backup_database_filename ){
        $this->database_exporter->setFileName($backup_database_filename);
        $this->database_exporter->runInternalDatabaseExport();
        $export_message = $this->database_exporter->getExportMessage();

        if( !$this->database_exporter->isExportedSuccessfully() ){
            $io->warning($export_message);
            return;
        }

        $sql_backup_filename = $this->database_exporter->getFileName() . $this->database_exporter->getDumpExtension();
        $sql_backup_path     = $this->database_exporter->getDumpedArchiveAbsolutePath();

        $this->archivizer->setArchiveName($sql_backup_filename);
        $this->archivizer->setDirectoriesToArchive([]);
        $this->archivizer->setFilesToArchive([$sql_backup_path]);
        $this->archivizer->handleArchivizing();

        $message = $this->archivizer->getArchivingStatus();
        if( $this->archivizer->isArchivedSuccessfully() ){
            $io->success($message);
            unlink($sql_backup_path);
        }else{
            $io->warning($message);
        }

    }

    /**
     * This function creates zip archive
     * @param SymfonyStyle $io
     * @param string $backup_files_filename
     */
    private function backupFiles(SymfonyStyle $io, string $backup_files_filename){

        $upload_dirs_for_modules = [
          MyImagesController::MODULE_NAME       => self::PUBLIC_DIR_ROOT . DIRECTORY_SEPARATOR . Env::getImagesUploadDir(),
          MyFilesController::MODULE_NAME        => self::PUBLIC_DIR_ROOT . DIRECTORY_SEPARATOR . Env::getFilesUploadDir(),
          ModulesController::MODULE_NAME_VIDEO  => self::PUBLIC_DIR_ROOT . DIRECTORY_SEPARATOR . Env::getVideoUploadDir(),
        ];

        $this->archivizer->setArchiveName($backup_files_filename);
        $this->archivizer->setDirectoriesToArchive($upload_dirs_for_modules);
        $this->archivizer->handleArchivizing();

        $message = $this->archivizer->getArchivingStatus();
        if( $this->archivizer->isArchivedSuccessfully() ){
            $io->success($message);
        }else{
            $io->warning($message);
        }
    }
}
