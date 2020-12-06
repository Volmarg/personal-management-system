<?php

namespace App\Services\Files\Archivizer;


use App\Controller\Core\Application;
use App\Services\Files\FilesHandler;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ZipArchivizer extends Archivizer
{
    /**
     * @var ZipArchive $zip
     */
    private $zip;

    public function setArchiveName(string $archive_name): void
    {
        $this->archive_name = $archive_name . self::EXTENSION_ZIP;
    }

    public function __construct(Application $app)
    {
        parent::__construct($app);


        if ( !class_exists('ZipArchive') ){
            throw new Exception("Class ZipArchive is not present. Install package 'php-zip'.");
        }

        $this->zip = new ZipArchive();
    }

    /**
     * This function will build archive for provided directories
     * @throws Exception
     */
    protected function buildArchive(){

        if ( !$this->zip->open($this->archive_full_path, ZipArchive::CREATE) ) {
            $this->setIsArchivedSuccessfully(false);
            $this->setArchivingStatus(self::EXPORT_MESSAGE_ARCHIVIZER_HAS_NO_PERMISSIONS_TO_SAVE);
            return;
        }

        foreach($this->directories_to_archive as $directory_to_archive ){
            $this->addRecursively($directory_to_archive);
        }

        foreach($this->files_to_archive as $file_to_archive){
            $archived_directory_path = pathinfo($file_to_archive, PATHINFO_DIRNAME);
            $this->addSingleFileToArchive($file_to_archive, $archived_directory_path, true);
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
     * @param $archived_directory_path
     * @throws Exception
     */
    private function addRecursively(string $archived_directory_path){

        if ( is_dir($archived_directory_path) ) {

            $iterator = new RecursiveDirectoryIterator($archived_directory_path);
            $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
            $resources_in_directory = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($resources_in_directory as $resource_found_in_archived_directory) {
                $resource_found_in_archived_directory = realpath($resource_found_in_archived_directory);

                $target_directory_regex = $this->rebuildSourceDirectoryForArchiveStructure($archived_directory_path);

                if ( is_dir($resource_found_in_archived_directory) ) {
                    $archived_directory = $this->extractArchiveDirectoryFromAbsolutePath($resource_found_in_archived_directory, $target_directory_regex);
                    $this->zip->addEmptyDir($archived_directory . DIRECTORY_SEPARATOR);
                } else if (is_file($resource_found_in_archived_directory) ) {
                    $this->addSingleFileToArchive($resource_found_in_archived_directory, $archived_directory_path);
                }
            }

        } else if (is_file($archived_directory_path) === true) {
            $this->zip->addFile($archived_directory_path);
        }

    }

    /**
     * Will add single file to the zip archive
     *
     * @param string $resource_found_in_archived_directory
     * @param string $archived_directory_path
     * @param bool $is_single_file
     *        - required to decide if the filepath (which is used to check if the file exists) is absolute or relative
     * @throws Exception
     */
    private function addSingleFileToArchive(string $resource_found_in_archived_directory, string $archived_directory_path, bool $is_single_file = false): void
    {
        $target_directory_regex = $this->rebuildSourceDirectoryForArchiveStructure($archived_directory_path);
        $archived_file = $this->extractArchiveFileFromAbsolutePath($resource_found_in_archived_directory, $target_directory_regex);

        /**
         * `$archived_file` has leading slash as it's needed to build the folder structure in the zip archive itself
         *  when adding the directories to the archive
         */
        $archived_file_path_in_project = $archived_file;
        if(!$is_single_file){
            $archived_file_path_in_project = FilesHandler::trimFirstAndLastSlash($archived_file);
        }

        if( !file_exists($archived_file_path_in_project) ){
            throw new Exception("Could not add file to the archive, no such file exist: {$archived_file_path_in_project}");
        }

        $this->zip->addFile($resource_found_in_archived_directory, $archived_file);
    }

}