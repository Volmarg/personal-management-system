<?php

namespace App\Services\Files;

use App\Controller\Core\Application;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Archivizer {

    const EXTENSION = '.zip';

    const EXPORT_MESSAGE_COULD_NOT_CREATE_FOLDER            = "Could not create folder for export.";

    const EXPORT_MESSAGE_BACKUP_DIRECTORY_DOES_NOT_EXIST    = 'Backup directory does not exist';

    const EXPORT_MESSAGE_EXPORT_FILE_DOES_NOT_EXIST         = "Exported file does not exist.";

    const EXPORT_MESSAGE_EXPORTED_DATABASE_IS_TO_SMALL      = "Exported zip size is small.";

    const EXPORT_MESSAGE_GENERAL_ERROR                      = "There was an error while building zip archive.";

    const EXPORT_MESSAGE_SUCCESS                            = "Archive has been successfully created";

    const EXPORT_MESSAGE_ZIP_HAS_NO_PERMISSIONS_TO_SAVE     = "No permissions to save in backup directory";

    const EXPORT_ERROR = "Archive export has failed!";

    const MINIMUM_BACKUP_SIZE = 1024000; // bytes ~1mb

    const FILE_PREFIX_MODE_CURRENT_DATE_TIME = 'FILE_PREFIX_MODE_CURRENT_DATE_TIME';

    /**
     * @var string
     */
    private $zipping_status;

    /**
     * @var string
     */
    private $archive_name;

    /**
     * @var string[]
     */
    private $directories_to_zip;

    /**
     * @var bool
     */
    private $zip_recursively = true;

    /**
     * @var bool
     */
    private $isZippedSuccessfully = false;

    /**
     * @var ZipArchive $zip
     */
    private $zip;

    /**
     * @var string
     */
    private $backup_directory;

    /**
     * Safety postfix - should be unique so that existing db sql for today won't be overwritten
     * @var string $file_postfix
     */
    private $file_prefix = '';

    /**
     * @var string
     */
    private $archive_full_path = '';

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @return string
     */
    public function getZippingStatus(): string {
        return $this->zipping_status;
    }

    /**
     * @param string $zipping_status
     */
    private function setZippingStatus(string $zipping_status): void {
        $this->zipping_status = $zipping_status;
    }

    /**
     * @return string
     */
    public function getArchiveName(): string {
        return $this->archive_name;
    }

    /**
     * @param string $archive_name
     */
    public function setArchiveName(string $archive_name): void {
        $this->archive_name = $archive_name . self::EXTENSION;
    }

    /**
     * @return  string[]
     */
    public function getDirectoriesToZip(): string {
        return $this->directories_to_zip;
    }

    /**
     * @param string[] $directory_to_zip
     */
    public function setDirectoriesToZip(array $directory_to_zip): void {
        $this->directories_to_zip = $directory_to_zip;
    }

    /**
     * @return bool
     */
    public function isZipRecursively(): bool {
        return $this->zip_recursively;
    }

    /**
     * @param bool $zip_recursively
     */
    public function setZipRecursively(bool $zip_recursively): void {
        $this->zip_recursively = $zip_recursively;
    }

    /**
     * @return bool
     */
    public function isZippedSuccessfully(): bool {
        return $this->isZippedSuccessfully;
    }

    /**
     * @param bool $isZippedSuccessfully
     */
    private function setIsZippedSuccessfully(bool $isZippedSuccessfully): void {
        $this->isZippedSuccessfully = $isZippedSuccessfully;
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
    private function getBackupDirectory(): string {
        return $this->backup_directory;
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
     * DatabaseExporter constructor.
     * @param Application $app
     * @throws \Exception
     */
    public function __construct(Application $app) {

        if ( !class_exists('ZipArchive') ){
            throw new \Exception("Class ZipArchive is not present. Install package 'php-zip'.");
        }

        $this->zip = new ZipArchive();
        $this->app = $app;

        $this->setFilePrefix(self::FILE_PREFIX_MODE_CURRENT_DATE_TIME);
    }

    public function zip(){

        $backup_directory_exists = file_exists($this->getBackupDirectory());

        if( !$backup_directory_exists ){
            $this->setZippingStatus(self::EXPORT_MESSAGE_BACKUP_DIRECTORY_DOES_NOT_EXIST);
            $this->setIsZippedSuccessfully(false);
            return;
        }

        try{
            $this->prepareBackupDirectory();
            $this->buildArchive();
            $this->checkArchive();
        }catch(\Exception $e){
            $this->app->logger->critical($e->getMessage());
            $this->app->logger->critical(self::EXPORT_ERROR,[
                'date' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
            $this->setZippingStatus(self::EXPORT_MESSAGE_GENERAL_ERROR);
            $this->setIsZippedSuccessfully(false);
        }

    }

    /**
     * This function will create directory with current date and it will be used to save zip inside it
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
            $this->setZippingStatus(self::EXPORT_MESSAGE_COULD_NOT_CREATE_FOLDER);
            $this->setIsZippedSuccessfully(false);
        }

        $this->setBackupDirectory($target_directory);
        $this->setArchiveFullPath($target_directory . DIRECTORY_SEPARATOR . $this->file_prefix .$this->archive_name);
    }

    /**
     * This function will check if the archive was created and it's size is withing range
     */
    private function checkArchive(){

        $is_dump_existing = file_exists($this->getArchiveFullPath());

        if( !$is_dump_existing ){
            $this->setIsZippedSuccessfully(false);
            $this->setZippingStatus(self::EXPORT_MESSAGE_EXPORT_FILE_DOES_NOT_EXIST);
        }else{
            $archive_size = filesize($this->getArchiveFullPath());

            if( self::MINIMUM_BACKUP_SIZE > $archive_size ){
                $this->setIsZippedSuccessfully(false);
                $this->setZippingStatus(self::EXPORT_MESSAGE_EXPORTED_DATABASE_IS_TO_SMALL);
                return;
            }

            $this->setIsZippedSuccessfully(true);
            $this->setZippingStatus(self::EXPORT_MESSAGE_SUCCESS);
        }

    }

    /**
     * This function will build archive for provided directories
     */
    private function buildArchive(){

        if ( !$this->zip->open($this->archive_full_path, ZipArchive::CREATE) ) {
            $this->setIsZippedSuccessfully(false);
            $this->setZippingStatus(self::EXPORT_MESSAGE_ZIP_HAS_NO_PERMISSIONS_TO_SAVE);
            return;
        }

        foreach( $this->directories_to_zip as $directory_to_zip ){
           $this->addRecursively($directory_to_zip);
        }

        if( ZipArchive::ER_OK !== $this->zip->status ){
            $this->app->logger->critical("Zip archive has returned error status", [
                $this->zip->status => self::getHumanReadableStatus($this->zip->status),
            ]);
        }

        if( 0 === $this->zip->numFiles ){
            $this->app->logger->critical("No files have been archived");
        }

        $this->zip->close();

    }

    /**
     * This function returns status code in human readable string
     * @param $status
     * @return string
     */
    private static function getHumanReadableStatus( $status )
    {
        switch( (int) $status )
        {
            case ZipArchive::ER_OK           : return 'N No error';
            case ZipArchive::ER_MULTIDISK    : return 'N Multi-disk zip archives not supported';
            case ZipArchive::ER_RENAME       : return 'S Renaming temporary file failed';
            case ZipArchive::ER_CLOSE        : return 'S Closing zip archive failed';
            case ZipArchive::ER_SEEK         : return 'S Seek error';
            case ZipArchive::ER_READ         : return 'S Read error';
            case ZipArchive::ER_WRITE        : return 'S Write error';
            case ZipArchive::ER_CRC          : return 'N CRC error';
            case ZipArchive::ER_ZIPCLOSED    : return 'N Containing zip archive was closed';
            case ZipArchive::ER_NOENT        : return 'N No such file';
            case ZipArchive::ER_EXISTS       : return 'N File already exists';
            case ZipArchive::ER_OPEN         : return 'S Can\'t open file';
            case ZipArchive::ER_TMPOPEN      : return 'S Failure to create temporary file';
            case ZipArchive::ER_ZLIB         : return 'Z Zlib error';
            case ZipArchive::ER_MEMORY       : return 'N Malloc failure';
            case ZipArchive::ER_CHANGED      : return 'N Entry has been changed';
            case ZipArchive::ER_COMPNOTSUPP  : return 'N Compression method not supported';
            case ZipArchive::ER_EOF          : return 'N Premature EOF';
            case ZipArchive::ER_INVAL        : return 'N Invalid argument';
            case ZipArchive::ER_NOZIP        : return 'N Not a zip archive';
            case ZipArchive::ER_INTERNAL     : return 'N Internal error';
            case ZipArchive::ER_INCONS       : return 'N Zip archive inconsistent';
            case ZipArchive::ER_REMOVE       : return 'S Can\'t remove file';
            case ZipArchive::ER_DELETED      : return 'N Entry has been deleted';

            default: return sprintf('Unknown status %s', $status );
        }
    }

    /**
     * This function will zip files recursively for given directory
     * Iterator makes archived structure a bit messy by adding absolute path that's why there is a bit dirty logic in
     *  we extract new path based on absolute path and replace it in archive itself
     * @param $source
     */
    private function addRecursively($source){

        if ( is_dir($source) ) {

            $iterator = new RecursiveDirectoryIterator($source);
            $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
            $files    = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = realpath($file);

                $target_directory_regex = $this->rebuildSourceDirectoryForArchiveStructure($source);

                if ( is_dir($file) ) {
                    $archived_directory = $this->extractArchiveDirectoryFromAbsolutePath($file, $target_directory_regex);
                    $this->zip->addEmptyDir($archived_directory . DIRECTORY_SEPARATOR);
                } else if (is_file($file) ) {
                    $archived_file = $this->extractArchiveFileFromAbsolutePath($file, $target_directory_regex);
                    $this->zip->addFile($file, $archived_file);
                }
            }

        } else if (is_file($source) === true) {
            $this->zip->addFile($source);
        }

    }

    /**
     * This is a dirty function but for zipping we provide "./public/..."
     * And for changing the structure inside archive itself we need "public/..."
     * Returns string usable for regex match - the result itself is useless - it's only needed for regex
     * @param string $source_path
     * @return string
     */
    private function rebuildSourceDirectoryForArchiveStructure(string $source_path): string {

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
    private function extractArchiveDirectoryFromAbsolutePath(string $absolute_path, string $target_directory_regex): string{

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
    private function extractArchiveFileFromAbsolutePath(string $absolute_path, string $target_directory_regex): string{

        preg_match('#' . $target_directory_regex . '(.*)#', $absolute_path, $matches);
        $archived_file = DIRECTORY_SEPARATOR . $matches[0];

        return $archived_file;
    }
}