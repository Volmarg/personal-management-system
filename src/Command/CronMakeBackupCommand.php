<?php

namespace App\Command;

use App\Controller\Files\FileUploadController;
use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Modules\Images\MyImagesController;
use App\Controller\Core\Env;
use App\Services\Database\DatabaseExporter;
use App\Services\Files\Archivizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CronMakeBackupCommand extends Command
{
    const BACKUP_TYPE_SQL   = 'sql';
    const BACKUP_TYPE_FILES = 'files';

    const BACKUP_DIRECTORY  = '/home/volmarg/Partycje/Dane/pms_db_backup';
    const BACKUP_DATABASE_FILENAME   = 'pmsSqlBackup';
    const BACKUP_FILES_FILENAME      = 'files';

    const OPTION_SKIP_FILES                 = 'skip-files';
    const OPTION_SKIP_UPLOAD_MODULE         = 'skip-upload-module';

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
     * @var Archivizer $archivizer
     */
    private $archivizer;

    public function __construct(DatabaseExporter $database_exporter, Archivizer $archivizer, string $name = null) {
        parent::__construct($name);
        $this->database_exporter = $database_exporter;
        $this->archivizer        = $archivizer;
    }

    protected function configure()
    {

        $backup_types         = implode(', ', self::ALL_BACKUPS_TYPES);
        $upload_modules_names = implode(', ', array_keys(FileUploadController::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES));

        $this
            ->setDescription('This command allows to make backup of: ' . $backup_types)
            ->addArgument(self::ARGUMENT_BACKUP_DIRECTORY_MODULE, InputArgument::REQUIRED,'Given directory will be used to store the backups')
            ->addArgument(self::ARGUMENT_BACKUP_DATABASE_FILENAME, InputArgument::REQUIRED,'Database backup will be saved under that file name')
            ->addArgument(self::ARGUMENT_BACKUP_FILES_FILENAME, InputArgument::REQUIRED,'Files backup will be saved under that file name')
            ->addOption(self::OPTION_SKIP_FILES, null,InputOption::VALUE_NONE, 'If set - will skip backing up the upload directory.')
            ->addOption(self::OPTION_SKIP_UPLOAD_MODULE, null, InputOption::VALUE_REQUIRED,
              "
                Will skip backup of files for given upload based module. Possible values: [{$upload_modules_names}]
                Use example: --skip-files=My\ Images,My\ Files (escaped spacebars).
              "
            )
            ->setHelp("bin/console cron:make-backup /backupDir databaseName filesName");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->note("Started backup process");
        {
            $argument_backup_directory          = $input->getArgument(self::ARGUMENT_BACKUP_DIRECTORY_MODULE);
            $argument_backup_database_filename  = $input->getArgument(self::ARGUMENT_BACKUP_DATABASE_FILENAME);
            $argument_backup_files_filename     = $input->getArgument(self::ARGUMENT_BACKUP_FILES_FILENAME);

            $option_skip_files         = $input->getOption(self::OPTION_SKIP_FILES);

            if ($option_skip_files) {
                $io->note(sprintf("Files backup will be skipped"));
            }

            if( !$option_skip_files ){

                try{
                    $skipped_modules = explode(',', $input->getOption(self::OPTION_SKIP_UPLOAD_MODULE));
                }catch(\Exception $e){
                    $io->error("Could not parse data for skipped modules. Did You provided valid values like in example?");
                    return false;
                }

                $this->backupFiles($io, $argument_backup_directory, $argument_backup_files_filename, $skipped_modules);
            }

            $this->backupDatabase($io, $argument_backup_directory, $argument_backup_database_filename);

        }
        $io->note("Backup process has been completed");
    }

    /**
     * This function creates database dump
     * @param SymfonyStyle $io
     * @param string $argument_backup_directory
     * @param string $backup_database_filename
     */
    private function backupDatabase(SymfonyStyle $io, string $argument_backup_directory, string $backup_database_filename ){
        $this->database_exporter->setFileName($backup_database_filename);
        $this->database_exporter->setBackupDirectory($argument_backup_directory);
        $this->database_exporter->runInternalDatabaseExport();
        $export_message = $this->database_exporter->getExportMessage();

        if( $this->database_exporter->isExportedSuccessfully() ){
            $io->success($export_message);
        }else{
            $io->warning($export_message);
        }
    }

    /**
     * This function creates zip archive
     * @param SymfonyStyle $io
     * @param string $argument_backup_directory
     * @param string $backup_files_filename
     * @param array $skipped_modules
     */
    private function backupFiles(SymfonyStyle $io, string $argument_backup_directory, string $backup_files_filename, array $skipped_modules = []){

        $upload_dirs_for_modules = [
          MyImagesController::MODULE_NAME => self::PUBLIC_DIR_ROOT . DIRECTORY_SEPARATOR . Env::getImagesUploadDir(),
          MyFilesController::MODULE_NAME  => self::PUBLIC_DIR_ROOT . DIRECTORY_SEPARATOR . Env::getFilesUploadDir(),
        ];

        foreach($skipped_modules as $skipped_module){
            if( array_key_exists($skipped_module, $upload_dirs_for_modules) ){
                unset($upload_dirs_for_modules[$skipped_module]);
            }
        }

        $this->archivizer->setBackupDirectory($argument_backup_directory);
        $this->archivizer->setZipRecursively(true);
        $this->archivizer->setArchiveName($backup_files_filename);
        $this->archivizer->setDirectoriesToZip($upload_dirs_for_modules);

        $this->archivizer->zip();
        $message = $this->archivizer->getZippingStatus();

        if( $this->archivizer->isZippedSuccessfully() ){
            $io->success($message);
        }else{
            $io->warning($message);
        }
    }
}
