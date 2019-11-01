<?php

namespace App\Command;

use App\Services\Database\DatabaseExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CronMakeBackupCommand extends Command
{
    const BACKUP_TYPE_SQL   = 'sql';
    const BACKUP_TYPE_FILES = 'files';

    const BACKUP_DIRECTORY  = '/home/volmarg/Partycje/Dane/pms_db_backup';

    const ARGUMENT_SKIP_FILES           = 'skip-files';
    const ARGUMENT_SKIP_UPLOAD_MODULE   = 'skip-upload-module';

    const ALL_BACKUPS_TYPES = [
        self::BACKUP_TYPE_FILES,
        self::BACKUP_TYPE_SQL,
    ];

    protected static $defaultName = 'cron:make-backup';

    /**
     * @var DatabaseExporter $database_exporter
     */
    private $database_exporter;

    public function __construct(DatabaseExporter $database_exporter, string $name = null) {
        parent::__construct($name);
        $this->database_exporter = $database_exporter;
    }

    protected function configure()
    {

        $backup_types = implode(', ', self::ALL_BACKUPS_TYPES);

        $this
            ->setDescription('This command allows to make backup of: ' . $backup_types)
            ->addArgument(self::ARGUMENT_SKIP_FILES, InputArgument::OPTIONAL, 'If set to true - will skip backing up the upload directory.')
            ->addArgument(self::ARGUMENT_SKIP_UPLOAD_MODULE, InputArgument::OPTIONAL, 'Will skip backup of files for given upload based module')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $argument_skip_files = $input->getArgument(self::ARGUMENT_SKIP_FILES);

        if ($argument_skip_files) {
            $io->note(sprintf("Files backup will be skipped"));
        }

        $this->database_exporter->setFileName('pmsSqlBackup');
        $this->database_exporter->setBackupDirectory(self::BACKUP_DIRECTORY);
        $this->database_exporter->runInternalDatabaseExport();
        $export_message = $this->database_exporter->getExportMessage();

        $io->note($export_message);

        $io->success("Backup has been done");
    }
}
