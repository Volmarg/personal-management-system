<?php

namespace App\Services\Files\Archivizer;

use App\Controller\Core\Application;

abstract class Archivizer {

    const EXTENSION_ZIP  = '.zip';

    const EXPORT_MESSAGE_COULD_NOT_CREATE_FOLDER            = "Could not create folder for export.";

    const EXPORT_MESSAGE_TARGET_DIRECTORY_DOES_NOT_EXIST    = 'Target directory does not exist';

    const EXPORT_MESSAGE_EXPORT_FILE_DOES_NOT_EXIST         = "Exported file does not exist.";

    const EXPORT_MESSAGE_EXPORTED_DATABASE_IS_TO_SMALL      = "Exported zip size is small.";

    const EXPORT_MESSAGE_GENERAL_ERROR                      = "There was an error while building zip archive.";

    const EXPORT_MESSAGE_SUCCESS                            = "Archive has been successfully created";

    const EXPORT_MESSAGE_ARCHIVIZER_HAS_NO_PERMISSIONS_TO_SAVE = "No permissions to save in target directory";

    const EXPORT_ERROR = "Archive export has failed!";

    const MINIMUM_ARCHIVE_SIZE = 1024000; // bytes ~1mb

    const FILE_PREFIX_MODE_CURRENT_DATE_TIME = 'FILE_PREFIX_MODE_CURRENT_DATE_TIME';

    /**
     * @var string
     */
    protected $archiving_status;

    /**
     * @var string
     */
    protected $archive_name;

    /**
     * @var string[]
     */
    protected $directories_to_archive = [];

    /**
     * @var array
     */
    protected array $files_to_archive = [];

    /**
     * @var bool
     */
    protected $archive_recursively = true;

    /**
     * @var bool
     */
    protected $is_archived_successfully = false;

    /**
     * @var string
     */
    protected $target_directory;

    /**
     * Archivizer modifies target directory with for example `current datetime`
     * @var string $used_directory
     */
    protected string $used_directory;

    /**
     * Safety postfix - should be unique so that existing db sql for today won't be overwritten
     * @var string $file_postfix
     */
    protected $file_prefix = '';

    /**
     * @var string
     */
    protected $archive_full_path = '';

    /**
     * @var Application $app
     */
    protected $app;

    /**
     * @return string
     */
    public function getArchivingStatus(): string {
        return $this->archiving_status;
    }

    /**
     * @param string $archiving_status
     */
    protected function setArchivingStatus(string $archiving_status): void {
        $this->archiving_status = $archiving_status;
    }

    /**
     * @return string
     */
    public function getArchiveName(): string {
        return $this->archive_name;
    }

    /**
     * @return  string[]
     */
    public function getDirectoriesToArchive(): string {
        return $this->directories_to_archive;
    }

    /**
     * @param string[] $directory_to_archive
     */
    public function setDirectoriesToArchive(array $directory_to_archive): void {
        $this->directories_to_archive = $directory_to_archive;
    }

    /**
     * @return string
     */
    public function getUsedDirectory(): string
    {
        return $this->used_directory;
    }

    /**
     * @param string $used_directory
     */
    public function setUsedDirectory(string $used_directory): void
    {
        $this->used_directory = $used_directory;
    }

    /**
     * @return bool
     */
    public function isArchiveRecursively(): bool {
        return $this->archive_recursively;
    }

    /**
     * @param bool $archive_recursively
     */
    public function setArchiveRecursively(bool $archive_recursively): void {
        $this->archive_recursively = $archive_recursively;
    }

    /**
     * @return bool
     */
    public function isArchivedSuccessfully(): bool {
        return $this->is_archived_successfully;
    }

    /**
     * @param bool $is_archived_successfully
     */
    protected function setIsArchivedSuccessfully(bool $is_archived_successfully): void {
        $this->is_archived_successfully = $is_archived_successfully;
    }

    /**
     * @param string $target_directory
     */
    public function setTargetDirectory(string $target_directory): void {
        $this->target_directory = $target_directory;
    }

    /**
     * @return string
     */
    protected function getTargetDirectory(): string {
        return $this->target_directory;
    }

    /**
     * @return string
     */
    protected function getFilePrefix(): string {
        return $this->file_prefix;
    }

    /**
     * @return array
     */
    public function getFilesToArchive(): array
    {
        return $this->files_to_archive;
    }

    /**
     * @param array $files_to_archive
     */
    public function setFilesToArchive(array $files_to_archive): void
    {
        $this->files_to_archive = $files_to_archive;
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
     * @return string
     */
    private function getArchiveFullPath(): string {
        return $this->archive_full_path;
    }

    /**
     * @param string $archive_full_path
     */
    private function setArchiveFullPath(string $archive_full_path): void {
        $this->archive_full_path = $archive_full_path;
    }

    /**
     * @param string $archive_name
     */
    public abstract function setArchiveName(string $archive_name): void;

    /**
     * This function will build archive for provided directories
     */
    protected abstract function buildArchive();

    /**
     * DatabaseExporter constructor.
     * @param Application $app
     * @throws \Exception
     */
    public function __construct(Application $app) {
        $this->app = $app;
        $this->setFilePrefix(self::FILE_PREFIX_MODE_CURRENT_DATE_TIME);
    }

    /**
     * Calls the logic for handling archivization
     */
    public function handleArchivizing(){

        $target_directory_exists = file_exists($this->getTargetDirectory());

        if( !$target_directory_exists ){
            $this->setArchivingStatus(self::EXPORT_MESSAGE_TARGET_DIRECTORY_DOES_NOT_EXIST);
            $this->setIsArchivedSuccessfully(false);
            return;
        }

        try{
            $this->prepareTargetDirectory();
            $this->buildArchive();
            $this->checkArchive();
        }catch(\Exception $e){
            $this->app->logger->critical($e->getMessage());
            $this->app->logger->critical(self::EXPORT_ERROR,[
                'date' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
            $this->setArchivingStatus(self::EXPORT_MESSAGE_GENERAL_ERROR);
            $this->setIsArchivedSuccessfully(false);
        }

    }

    /**
     * This function will create directory with current date and it will be used to save zip inside it
     * Also sets target directory so that it points to the folder with current date
     */
    private function prepareTargetDirectory():void {
        $curr_date_time = new \DateTime();
        $curr_date      = $curr_date_time->format('Y-m-d');

        $target_directory = $this->getTargetDirectory();

        $used_target_directory = $target_directory . DIRECTORY_SEPARATOR . $curr_date;

        $dir_exists = file_exists($used_target_directory);

        // first checking if it exist and if not then create it
        if( !$dir_exists ){
            mkdir($used_target_directory);
        }

        // this is safety check, if folder didnt existed and still does not exist then it's undesired state
        if( !$dir_exists ){
            $this->setArchivingStatus(self::EXPORT_MESSAGE_COULD_NOT_CREATE_FOLDER);
            $this->setIsArchivedSuccessfully(false);
        }

        $this->setUsedDirectory($used_target_directory);
        $this->setArchiveFullPath($used_target_directory . DIRECTORY_SEPARATOR . $this->file_prefix .$this->archive_name);
    }

    /**
     * This function will check if the archive was created and it's size is withing range
     */
    private function checkArchive(){

        $is_archive_existing = file_exists($this->getArchiveFullPath());

        if( !$is_archive_existing ){
            $this->setIsArchivedSuccessfully(false);
            $this->setArchivingStatus(self::EXPORT_MESSAGE_EXPORT_FILE_DOES_NOT_EXIST);
        }else{
            $archive_size = filesize($this->getArchiveFullPath());

            if( self::MINIMUM_ARCHIVE_SIZE > $archive_size ){
                $this->setIsArchivedSuccessfully(false);
                $this->setArchivingStatus(self::EXPORT_MESSAGE_EXPORTED_DATABASE_IS_TO_SMALL);
                return;
            }

            $this->setIsArchivedSuccessfully(true);
            $this->setArchivingStatus(self::EXPORT_MESSAGE_SUCCESS);
        }

    }

    /**
     * This is a dirty function but for zipping we provide "./public/..."
     * And for changing the structure inside archive itself we need "public/..."
     * Returns string usable for regex match - the result itself is useless - it's only needed for regex
     * @param string $source_path
     * @return string
     */
    protected function rebuildSourceDirectoryForArchiveStructure(string $source_path): string {

        $target_directory   = $source_path;
        $dot_position       = strpos($source_path, DOT);
        $slash_position     = strpos($source_path, DIRECTORY_SEPARATOR);

        if( !is_bool($dot_position) && 0 === $dot_position ){
            $target_directory = str_replace(DOT . DIRECTORY_SEPARATOR, '', $source_path); // escape dot
        }elseif(!is_bool($slash_position) && 0 === $slash_position){
            $target_directory = substr($source_path, 1);    // escape first slash
        }

        $target_directory_regex = str_replace( DIRECTORY_SEPARATOR , "\\" . DIRECTORY_SEPARATOR, $target_directory); //escape chars for regex

        return $target_directory_regex;
    }

    /**
     * Extract the directory to make in archive by finding in absolute path everything from $target_directory_regex path to the end
     * For example from: /var/www/html/personal-management-system-dev/public/upload/images/123/2/4/test/testo2123/
     * This will be extracted: public/upload/images/123/2/4/test/testo2123/
     * @param string $absolute_path
     * @param string $target_directory_regex
     * @return string
     */
    protected function extractArchiveDirectoryFromAbsolutePath(string $absolute_path, string $target_directory_regex): string{

        preg_match('#' . $target_directory_regex . '(.*)[^\/]#', $absolute_path, $matches);
        $archived_directory = DIRECTORY_SEPARATOR . $matches[0];

        return $archived_directory;
    }

    /**
     * Extract the file to add in archive by finding in absolute path everything from $target_directory_regex path to the end
     * For example from: /var/www/html/personal-management-system-dev/public/upload/images/123/2/4/test/testo2123/file.jpg
     * This will be extracted: public/upload/images/123/2/4/test/testo2123/file.jpg
     * @param string $absolute_path
     * @param string $target_directory_regex
     * @return string
     */
    protected function extractArchiveFileFromAbsolutePath(string $absolute_path, string $target_directory_regex): string{

        preg_match('#' . $target_directory_regex . '(.*)#', $absolute_path, $matches);
        $archived_file = DIRECTORY_SEPARATOR . $matches[0];

        return $archived_file;
    }
}