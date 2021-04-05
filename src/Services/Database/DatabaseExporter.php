<?php

namespace App\Services\Database;

use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\DTO\DatabaseCredentialsDTO;
use Monolog\Logger;

/**
 * This exporter relies on shell commands
 * It was created to export the database to which symfony is currently connected to
 * Class DatabaseExporter
 */
class DatabaseExporter {

    const DUMP_EXTENSION_SQL = '.sql';

    const EXPORT_MESSAGE_COULD_NOT_CREATE_FOLDER            = "Could not create folder for export.";

    const EXPORT_MESSAGE_BACKUP_DIRECTORY_DOES_NOT_EXIST    = 'Backup directory does not exist';

    const EXPORT_MESSAGE_EXPORT_FILE_DOES_NOT_EXIST         = "Exported file does not exist.";

    const EXPORT_MESSAGE_EXPORTED_DATABASE_IS_TO_SMALL      = "Exported database size is small.";

    const EXPORT_MESSAGE_GENERAL_ERROR                      = "There was an error while performing mysql dump.";

    const EXPORT_MESSAGE_SUCCESS                            = "Database has been successfully exported";

    const EXPORT_ERROR = "Database dump has failed!";

    const MINIMUM_BACKUP_SIZE = 102400; // bytes = 100kb

    const FILE_PREFIX_MODE_CURRENT_DATE_TIME = 'FILE_PREFIX_MODE_CURRENT_DATE_TIME';

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $backupDirectory;

    /**
     * @var bool
     */
    private $isExportedSuccessfully = false;

    /**
     * @var string
     */
    private $exportMessage;

    /**
     * @var string
     */
    private $dumpExtension = '';

    /**
     * @var string
     */
    private $dumpFullPath;

    /**
     * Safety postfix - should be unique so that existing db sql for today won't be overwritten
     * @var string $filePrefix
     */
    private $filePrefix = '';

    /**
     * @var Logger $app
     */
    private $app;

    /**
     * @return string
     */
    public function getFileName(): string {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName): void {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getBackupDirectory(): string {
        return $this->backupDirectory;
    }

    /**
     * @return bool
     */
    public function isExportedSuccessfully(): bool {
        return $this->isExportedSuccessfully;
    }

    /**
     * @param bool $isExportedSuccessfully
     */
    private function setIsExportedSuccessfully(bool $isExportedSuccessfully): void {
        $this->isExportedSuccessfully = $isExportedSuccessfully;
    }

    /**
     * Returns the full path containing exported file name (which is modified by exporter)
     * @return string
     */
    public function getDumpedArchiveAbsolutePath(): string
    {
        return $this->dumpFullPath;
    }

    /**
     * @return string
     */
    public function getDumpExtension(): string {
        return $this->dumpExtension;
    }

    /**
     * @return string
     */
    private function getDumpFullPath(): string {
        return $this->dumpFullPath;
    }

    /**
     * @param string $dumpFullPath
     */
    private function setDumpFullPath(string $dumpFullPath): void {
        $this->dumpFullPath = $dumpFullPath;
    }

    /**
     * @param string $dumpExtension
     */
    public function setDumpExtension(?string $dumpExtension = null): void {

        if( empty($dumpExtension )){
            $this->dumpExtension = self::DUMP_EXTENSION_SQL;
            return;
        }
        $this->dumpExtension = $dumpExtension;
    }

    /**
     * @return string
     */
    public function getExportMessage(): string {
        return $this->exportMessage;
    }

    /**
     * @param string $exportMessage
     */
    private function setExportMessage(string $exportMessage): void {
        $this->exportMessage = $exportMessage;
    }

    /**
     * @param string $backupDirectory
     */
    public function setBackupDirectory(string $backupDirectory): void {
        $this->backupDirectory = $backupDirectory;
    }

    /**
     * @return string
     */
    private function getFilePrefix(): string {
        return $this->filePrefix;
    }

    /**
     * @param string $mode
     * @throws \Exception
     */
    public function setFilePrefix(string $mode = null): void {

        if( is_null($mode) ){
            $mode = self::FILE_PREFIX_MODE_CURRENT_DATE_TIME;
        }

        switch( $mode ){
            case self::FILE_PREFIX_MODE_CURRENT_DATE_TIME:
                    $currDateTime = new \DateTime();
                    $prefix = $currDateTime->format('Y_m_d_H_i_s_');
                break;
            default:
                throw new \Exception("This mode is not supported: {$mode}");
        }

        $this->filePrefix = $prefix;
    }

    /**
     * DatabaseExporter constructor.
     * @param Application $app
     * @throws \Exception
     */
    public function __construct(Application $app) {
        $this->app = $app;
        $this->setDumpExtension();
        $this->setFilePrefix(self::FILE_PREFIX_MODE_CURRENT_DATE_TIME);
    }

    /**
     * This function will create dump of database to which symfony is connected to
     *  Credentials are taken from '.env' file
     */
    public function runInternalDatabaseExport(){

        $backupDirectoryExists = file_exists($this->getBackupDirectory());

        if( !$backupDirectoryExists ){
            $this->setExportMessage(self::EXPORT_MESSAGE_BACKUP_DIRECTORY_DOES_NOT_EXIST);
            $this->setIsExportedSuccessfully(false);
            return;
        }

        try{
            $dto = Env::getDatabaseCredentials();
            $this->prepareBackupDirectory();

            $databaseDumpCommand = $this->buildShellMysqlDumpCommand($dto);

            $this->performDumpCommand($databaseDumpCommand);
            $this->checkDump();
        }catch(\Exception $e){
            $this->app->logger->critical($e->getMessage());
            $this->app->logger->critical(self::EXPORT_ERROR,[
                'date' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
            $this->setExportMessage(self::EXPORT_MESSAGE_GENERAL_ERROR);
            $this->setIsExportedSuccessfully(false);
        }
    }

    /**
     * This function will create directory with current date and it will be used to dump sql inside it
     * Also sets backup directory so that it points to the folder with current date
     */
    private function prepareBackupDirectory():void {
        $currDateTime = new \DateTime();
        $currDate     = $currDateTime->format('Y-m-d');

        $backupDirectory = $this->getBackupDirectory();
        $targetDirectory = $backupDirectory . DIRECTORY_SEPARATOR . $currDate;

        $dirExists = file_exists($targetDirectory);

        // first checking if it exist and if not then create it
        if( !$dirExists ){
            mkdir($targetDirectory);
        }

        // this is safety check, if folder didnt existed and still does not exist then it's undesired state
        if( !$dirExists ){
            $this->setExportMessage(self::EXPORT_MESSAGE_COULD_NOT_CREATE_FOLDER);
            $this->setIsExportedSuccessfully(false);
        }

        $this->setBackupDirectory($targetDirectory);
    }

    /**
     * Will build shell based mysql dump command depending on provided data
     * while for example password can be empty (this is allowed) and if so the params must be different
     * @param DatabaseCredentialsDTO $dto
     * @return string
     */
    private function buildShellMysqlDumpCommand(DatabaseCredentialsDTO $dto): string{

        $login      = $dto->getDatabaseLogin();
        $password   = $dto->getDatabasePassword();
        $host       = $dto->getDatabaseHost();
        $port       = $dto->getDatabasePort();
        $name       = $dto->getDatabaseName();

        $dumpExtension  = $this->getDumpExtension();
        $dumpLocation   = $this->getBackupDirectory();
        $dumpFilename   = $this->getFileName();
        $prefix         = $this->getFilePrefix();

        $dumpFullPath = $dumpLocation . DIRECTORY_SEPARATOR . $prefix . $dumpFilename . $dumpExtension;
        $this->setDumpFullPath($dumpFullPath);

        $command = "mysqldump -u " . $login;

        if( !empty($password) ){
            $command .= ' -p' . $password;
        }

        $portPattern = "";
        if( !empty($port) ){
            $portPattern = "--port={$port}";
        }

        $command .= " -h {$host} {$portPattern} {$name} > {$dumpFullPath}";

        return $command;
    }

    /**
     * This function will execute dump command
     * @param string $databaseDumpCommand
     */
    private function performDumpCommand(string $databaseDumpCommand): void {
        exec($databaseDumpCommand);
    }

    /**
     * This function will check if the dump was created and it's size is withing range
     */
    private function checkDump(){

        $isDumpExisting = file_exists($this->getDumpedArchiveAbsolutePath());

        if( !$isDumpExisting ){
            $this->setIsExportedSuccessfully(false);
            $this->setExportMessage(self::EXPORT_MESSAGE_EXPORT_FILE_DOES_NOT_EXIST);
        }else{
            $dumpSize = filesize($this->getDumpedArchiveAbsolutePath());

            if( self::MINIMUM_BACKUP_SIZE > $dumpSize ){
                $this->setIsExportedSuccessfully(false);
                $this->setExportMessage(self::EXPORT_MESSAGE_EXPORTED_DATABASE_IS_TO_SMALL);
                return;
            }

            $this->setIsExportedSuccessfully(true);
            $this->setExportMessage(self::EXPORT_MESSAGE_SUCCESS);
        }

    }
}