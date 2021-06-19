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
    protected $archivingStatus;

    /**
     * @var string
     */
    protected $archiveName;

    /**
     * @var string[]
     */
    protected $directoriesToArchive = [];

    /**
     * @var array
     */
    protected array $filesToArchive = [];

    /**
     * @var bool
     */
    protected $archiveRecursively = true;

    /**
     * @var bool
     */
    protected $isArchivedSuccessfully = false;

    /**
     * @var string
     */
    protected $targetDirectory;

    /**
     * Archivizer modifies target directory with for example `current datetime`
     * @var string $usedDirectory
     */
    protected string $usedDirectory;

    /**
     * Safety postfix - should be unique so that existing db sql for today won't be overwritten
     * @var string $filePrefix
     */
    protected $filePrefix = '';

    /**
     * @var string
     */
    protected $archiveFullPath = '';

    /**
     * @var Application $app
     */
    protected $app;

    /**
     * @var int $minimalArchiveSize
     */
    private int $minimalArchiveSize = self::MINIMUM_ARCHIVE_SIZE;

    /**
     * @return string
     */
    public function getArchivingStatus(): string {
        return $this->archivingStatus;
    }

    /**
     * @param string $archivingStatus
     */
    protected function setArchivingStatus(string $archivingStatus): void {
        $this->archivingStatus = $archivingStatus;
    }

    /**
     * @return string
     */
    public function getArchiveName(): string {
        return $this->archiveName;
    }

    /**
     * @return  string[]
     */
    public function getDirectoriesToArchive(): string {
        return $this->directoriesToArchive;
    }

    /**
     * @param string[] $directoryToArchive
     */
    public function setDirectoriesToArchive(array $directoryToArchive): void {
        $this->directoriesToArchive = $directoryToArchive;
    }

    /**
     * @return string
     */
    public function getUsedDirectory(): string
    {
        return $this->usedDirectory;
    }

    /**
     * @param string $usedDirectory
     */
    public function setUsedDirectory(string $usedDirectory): void
    {
        $this->usedDirectory = $usedDirectory;
    }

    /**
     * @return bool
     */
    public function isArchiveRecursively(): bool {
        return $this->archiveRecursively;
    }

    /**
     * @param bool $archiveRecursively
     */
    public function setArchiveRecursively(bool $archiveRecursively): void {
        $this->archiveRecursively = $archiveRecursively;
    }

    /**
     * @return bool
     */
    public function isArchivedSuccessfully(): bool {
        return $this->isArchivedSuccessfully;
    }

    /**
     * @param bool $isArchivedSuccessfully
     */
    protected function setIsArchivedSuccessfully(bool $isArchivedSuccessfully): void {
        $this->isArchivedSuccessfully = $isArchivedSuccessfully;
    }

    /**
     * @param string $targetDirectory
     */
    public function setTargetDirectory(string $targetDirectory): void {
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * @return string
     */
    protected function getTargetDirectory(): string {
        return $this->targetDirectory;
    }

    /**
     * @return string
     */
    protected function getFilePrefix(): string {
        return $this->filePrefix;
    }

    /**
     * @return array
     */
    public function getFilesToArchive(): array
    {
        return $this->filesToArchive;
    }

    /**
     * @param array $filesToArchive
     */
    public function setFilesToArchive(array $filesToArchive): void
    {
        $this->filesToArchive = $filesToArchive;
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
     * @return string
     */
    private function getArchiveFullPath(): string {
        return $this->archiveFullPath;
    }

    /**
     * @param string $archiveFullPath
     */
    private function setArchiveFullPath(string $archiveFullPath): void {
        $this->archiveFullPath = $archiveFullPath;
    }

    /**
     * @return int
     */
    public function getMinimalArchiveSize(): int
    {
        return $this->minimalArchiveSize;
    }

    /**
     * @param int $minimalArchiveSize
     */
    public function setMinimalArchiveSize(int $minimalArchiveSize): void
    {
        $this->minimalArchiveSize = $minimalArchiveSize;
    }

    /**
     * @param string $archiveName
     */
    public abstract function setArchiveName(string $archiveName): void;

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

        $targetDirectoryExists = file_exists($this->getTargetDirectory());

        if( !$targetDirectoryExists ){
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
        $currDateTime = new \DateTime();
        $currDate      = $currDateTime->format('Y-m-d');

        $targetDirectory     = $this->getTargetDirectory();
        $usedTargetDirectory = $targetDirectory . DIRECTORY_SEPARATOR . $currDate;

        $dirExists = file_exists($usedTargetDirectory);

        // first checking if it exist and if not then create it
        if( !$dirExists ){
            mkdir($usedTargetDirectory);
        }

        // this is safety check, if folder didnt existed and still does not exist then it's undesired state
        if( !$dirExists ){
            $this->setArchivingStatus(self::EXPORT_MESSAGE_COULD_NOT_CREATE_FOLDER);
            $this->setIsArchivedSuccessfully(false);
        }

        $this->setUsedDirectory($usedTargetDirectory);
        $this->setArchiveFullPath($usedTargetDirectory . DIRECTORY_SEPARATOR . $this->filePrefix .$this->archiveName);
    }

    /**
     * This function will check if the archive was created and it's size is withing range
     */
    private function checkArchive(){

        $isArchiveExisting = file_exists($this->getArchiveFullPath());

        if( !$isArchiveExisting ){
            $this->setIsArchivedSuccessfully(false);
            $this->setArchivingStatus(self::EXPORT_MESSAGE_EXPORT_FILE_DOES_NOT_EXIST);
        }else{
            $archiveSize = filesize($this->getArchiveFullPath());

            if( $this->getMinimalArchiveSize() > $archiveSize ){
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
     * @param string $sourcePath
     * @return string
     */
    protected function rebuildSourceDirectoryForArchiveStructure(string $sourcePath): string {

        $targetDirectory = $sourcePath;
        $dotPosition     = strpos($sourcePath, DOT);
        $slashPosition   = strpos($sourcePath, DIRECTORY_SEPARATOR);

        if( !is_bool($dotPosition) && 0 === $dotPosition ){
            $targetDirectory = str_replace(DOT . DIRECTORY_SEPARATOR, '', $sourcePath); // escape dot
        }elseif(!is_bool($slashPosition) && 0 === $slashPosition){
            $targetDirectory = substr($sourcePath, 1);    // escape first slash
        }

        $targetDirectoryRegex = str_replace( DIRECTORY_SEPARATOR , "\\" . DIRECTORY_SEPARATOR, $targetDirectory); //escape chars for regex

        return $targetDirectoryRegex;
    }

    /**
     * Extract the directory to make in archive by finding in absolute path everything from $target_directory_regex path to the end
     * For example from: /var/www/html/personal-management-system-dev/public/upload/images/123/2/4/test/testo2123/
     * This will be extracted: public/upload/images/123/2/4/test/testo2123/
     * @param string $absolutePath
     * @param string $targetDirectoryRegex
     * @return string
     */
    protected function extractArchiveDirectoryFromAbsolutePath(string $absolutePath, string $targetDirectoryRegex): string{

        preg_match('#' . $targetDirectoryRegex . '(.*)[^\/]#', $absolutePath, $matches);
        $archivedDirectory = DIRECTORY_SEPARATOR . $matches[0];

        return $archivedDirectory;
    }

    /**
     * Extract the file to add in archive by finding in absolute path everything from $target_directory_regex path to the end
     * For example from: /var/www/html/personal-management-system-dev/public/upload/images/123/2/4/test/testo2123/file.jpg
     * This will be extracted: public/upload/images/123/2/4/test/testo2123/file.jpg
     * @param string $absolutePath
     * @param string $targetDirectoryRegex
     * @return string
     */
    protected function extractArchiveFileFromAbsolutePath(string $absolutePath, string $targetDirectoryRegex): string{

        preg_match('#' . $targetDirectoryRegex . '(.*)#', $absolutePath, $matches);
        $archivedFile = DIRECTORY_SEPARATOR . $matches[0];

        return $archivedFile;
    }
}
