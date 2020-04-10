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

    const DUMP_EXTENSION_SQL = 'sql';

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
    private $file_name;

    /**
     * @var string
     */
    private $backup_directory;

    /**
     * @var bool
     */
    private $is_exported_successfully = false;

    /**
     * @var string
     */
    private $export_message;

    /**
     * @var string
     */
    private $dump_extension = '';

    /**
     * @var string
     */
    private $dump_full_path;

    /**
     * Safety postfix - should be unique so that existing db sql for today won't be overwritten
     * @var string $file_postfix
     */
    private $file_prefix = '';

    /**
     * @var Logger $app
     */
    private $app;

    /**
     * @return string
     */
    public function getFileName(): string {
        return $this->file_name;
    }

    /**
     * @param string $file_name
     */
    public function setFileName(string $file_name): void {
        $this->file_name = $file_name;
    }

    /**
     * @return string
     */
    public function getBackupDirectory(): string {
        return $this->backup_directory;
    }

    /**
     * @return bool
     */
    public function isExportedSuccessfully(): bool {
        return $this->is_exported_successfully;
    }

    /**
     * @param bool $is_exported_successfully
     */
    private function setIsExportedSuccessfully(bool $is_exported_successfully): void {
        $this->is_exported_successfully = $is_exported_successfully;
    }

    /**
     * @return string
     */
    public function getDumpExtension(): string {
        return $this->dump_extension;
    }

    /**
     * @return string
     */
    private function getDumpFullPath(): string {
        return $this->dump_full_path;
    }

    /**
     * @param string $dump_full_path
     */
    private function setDumpFullPath(string $dump_full_path): void {
        $this->dump_full_path = $dump_full_path;
    }

    /**
     * @param string $dump_extension
     */
    public function setDumpExtension(?string $dump_extension = null): void {

        if( empty($dump_extension )){
            $this->dump_extension = DOT . self::DUMP_EXTENSION_SQL;
            return;
        }
        $this->dump_extension = DOT . $dump_extension;
    }

    /**
     * @return string
     */
    public function getExportMessage(): string {
        return $this->export_message;
    }

    /**
     * @param string $export_message
     */
    private function setExportMessage(string $export_message): void {
        $this->export_message = $export_message;
    }

    /**
     * @param string $backup_directory
     */
    public function setBackupDirectory(string $backup_directory): void {
        $this->backup_directory = $backup_directory;
    }

    /**
     * @return string
     */
    private function getFilePrefix(): string {
        return $this->file_prefix;
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
                    $curr_date_time = new \DateTime();
                    $prefix = $curr_date_time->format('Y_m_d_H_i_s_');
                break;
            default:
                throw new \Exception("This mode is not supported: {$mode}");
        }

        $this->file_prefix = $prefix;
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

        $backup_directory_exists = file_exists($this->getBackupDirectory());

        if( !$backup_directory_exists ){
            $this->setExportMessage(self::EXPORT_MESSAGE_BACKUP_DIRECTORY_DOES_NOT_EXIST);
            $this->setIsExportedSuccessfully(false);
            return;
        }

        try{
            $dto = Env::getDatabaseCredentials();
            $this->prepareBackupDirectory();

            $database_dump_command = $this->buildShellMysqlDumpCommand($dto);

            $this->performDumpCommand($database_dump_command);
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
        $curr_date_time = new \DateTime();
        $curr_date      = $curr_date_time->format('Y-m-d');

        $backup_directory = $this->getBackupDirectory();

        $target_directory = $backup_directory . DIRECTORY_SEPARATOR . $curr_date;

        $dir_exists = file_exists($target_directory);

        // first checking if it exist and if not then create it
        if( !$dir_exists ){
            mkdir($target_directory);
        }

        // this is safety check, if folder didnt existed and still does not exist then it's undesired state
        if( !$dir_exists ){
            $this->setExportMessage(self::EXPORT_MESSAGE_COULD_NOT_CREATE_FOLDER);
            $this->setIsExportedSuccessfully(false);
        }

        $this->setBackupDirectory($target_directory);
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

        $dump_extension  = $this->getDumpExtension();
        $dump_location   = $this->getBackupDirectory();
        $dump_filename   = $this->getFileName();
        $prefix          = $this->getFilePrefix();

        $dump_full_path = $dump_location . DIRECTORY_SEPARATOR . $prefix . $dump_filename . $dump_extension;
        $this->setDumpFullPath($dump_full_path);

        $command = "mysqldump -u " . $login;

        if( !empty($password) ){
            $command .= ' -p' . $password;
        }

        $command .= " -h {$host} --port={$port} {$name} > {$dump_full_path}";

        return $command;
    }

    /**
     * This function will execute dump command
     * @param string $database_dump_command
     */
    private function performDumpCommand(string $database_dump_command): void {
        exec($database_dump_command);
    }

    /**
     * This function will check if the dump was created and it's size is withing range
     */
    private function checkDump(){

        $is_dump_existing = file_exists($this->getDumpFullPath());

        if( !$is_dump_existing ){
            $this->setIsExportedSuccessfully(false);
            $this->setExportMessage(self::EXPORT_MESSAGE_EXPORT_FILE_DOES_NOT_EXIST);
        }else{
            $dump_size = filesize($this->getDumpFullPath());

            if( self::MINIMUM_BACKUP_SIZE > $dump_size ){
                $this->setIsExportedSuccessfully(false);
                $this->setExportMessage(self::EXPORT_MESSAGE_EXPORTED_DATABASE_IS_TO_SMALL);
                return;
            }

            $this->setIsExportedSuccessfully(true);
            $this->setExportMessage(self::EXPORT_MESSAGE_SUCCESS);
        }

    }
}