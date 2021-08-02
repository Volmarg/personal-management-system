<?php

namespace App\Services\Files;

use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Controller\Utils\Utils;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This service is responsible for handling files in terms of internal usage, like moving/renaming/etc...
 * Class FilesHandler
 * @package App\Services
 */

class FilesHandler {

    const KEY_CURRENT_UPLOAD_MODULE_DIR = 'current_upload_module_dir';
    const KEY_TARGET_MODULE_UPLOAD_DIR  = 'target_upload_module_dir';
    const KEY_URL_CALLED_FROM           = 'url_called_from';
    const KEY_CURRENT_SUBDIRECTORY_NAME = 'current_subdirectory_name';
    const KEY_TARGET_SUBDIRECTORY_NAME  = 'target_subdirectory_name';
    const KEY_FILE_FULL_PATH            = 'file_full_path';
    const KEY_FILES_FULL_PATHS          = 'files_full_paths';
    const KEY_FILE_NEW_NAME             = 'file_new_name';
    const KEY_FILE_CURRENT_PATH         = 'file_current_location';
    const KEY_FILES_CURRENT_PATHS       = 'files_current_locations';
    const KEY_FILE_NEW_PATH             = 'file_new_location';
    const KEY_MODULES_NAMES             = 'modules_names';

    const FILE_KEY                      = 'file';

    const FILE_PATH_IS_EMPTY_EXCEPTION_MESSAGE = 'File path is empty';

    const KEY_UPLOAD_DIR = "upload";

    /**
     * @var Application $application
     */
    private $application;

    /**
     * @var DirectoriesHandler $directoriesHandle
     */
    private $directoriesHandle;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var FileTagger $fileTagger
     */
    private $fileTagger;

    /**
     * @var ImageHandler $imageHandler
     */
    private $imageHandler;


    public function __construct(Application $application, DirectoriesHandler $directoriesHandler, LoggerInterface $logger, FileTagger $fileTagger, ImageHandler $imageHandler) {
        $this->application       = $application;
        $this->directoriesHandle = $directoriesHandler;
        $this->imageHandler      = $imageHandler;
        $this->logger            = $logger;
        $this->fileTagger        = $fileTagger;
    }

    /**
     * @Route("/upload/action/copy-folder-data", name="upload_copy_folder_data", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function copyFolderDataToAnotherFolderByPostRequest(Request $request) {

        $currentUploadModuleDir  = $request->query->get(static::KEY_CURRENT_UPLOAD_MODULE_DIR);
        $targetUploadModuleDir   = $request->query->get(static::KEY_TARGET_MODULE_UPLOAD_DIR);
        $currentDirectoryPathInModuleUploadDir  = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);
        $targetDirectoryPathInModuleUploadDir   = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);

        $response = $this->copyFolderDataToAnotherFolder(
            $currentUploadModuleDir,
            $targetUploadModuleDir,
            $currentDirectoryPathInModuleUploadDir,
            $targetDirectoryPathInModuleUploadDir
        );

        return $response;
    }

    /**
     * @param string $currentUploadType
     * @param string $targetUploadType
     * @param string $currentDirectoryPathInModuleUploadDir
     * @param string $targetDirectoryPathInModuleUploadDir
     * @return Response
     * @throws Exception
     */
    public function copyFolderDataToAnotherFolder(
        ?string $currentUploadType,
        ?string $targetUploadType,
        ?string $currentDirectoryPathInModuleUploadDir,
        ?string $targetDirectoryPathInModuleUploadDir
    ){
        $currentSubdirectoryName = basename($currentDirectoryPathInModuleUploadDir);
        $targetSubdirectoryName  = basename($targetDirectoryPathInModuleUploadDir);

        $message = $this->application->translator->translate('logs.files.startedCopyingDataBetweenFoldersViaPost');

        $this->logger->info($message, [
            'current_upload_type'          => $currentUploadType,
            'target_upload_type'           => $targetUploadType,
            'current_subdirectory_name'    => $currentSubdirectoryName,
            'target_subdirectory_name'     => $targetSubdirectoryName,
            'current_directory_path_in_module_upload_dir' => $currentDirectoryPathInModuleUploadDir,
            'target_directory_path_in_module_upload_dir'  => $targetDirectoryPathInModuleUploadDir,
        ]);

        if ( empty($currentUploadType) ) {
            $message = $this->application->translator->translate('responses.files.currentUploadTypeIsMissingInRequest');
            return new Response($message, 500);
        }

        if ( empty($targetUploadType) ) {
            $message = $this->application->translator->translate('responses.files.targetUploadTypeIsMissingInRequest');
            return new Response($message, 500);
        }

        if ( empty($currentDirectoryPathInModuleUploadDir) ) {
            $message = $this->application->translator->translate('responses.files.currentSubdirectoryPathIsMissingInRequest');
            return new Response($message, 500);
        }

        if ( empty($targetDirectoryPathInModuleUploadDir) ) {
            $message = $this->application->translator->translate('responses.files.targetSubdirectoryPathIsMissingInRequest');
            return new Response($message, 500);
        }

        if(
                ( $currentUploadType === $targetUploadType )
            &&  ( $currentSubdirectoryName === $targetSubdirectoryName )
        ){
            $message = $this->application->translator->translate('responses.files.cannotCopyDataToTheSameFolderForGivenModule');
            return new Response($message. 500);
        }

        $currentTargetDirectory = FileUploadController::getTargetDirectoryForUploadModuleDir($currentUploadType);
        $newTargetDirectory     = FileUploadController::getTargetDirectoryForUploadModuleDir($targetUploadType);

        # checking if it's not main dir on any side
        if(
                $currentTargetDirectory === $currentDirectoryPathInModuleUploadDir
            ||  $newTargetDirectory     === $targetDirectoryPathInModuleUploadDir
        ){
            if( // both are main
                    $currentTargetDirectory === $currentDirectoryPathInModuleUploadDir
                &&  $newTargetDirectory     === $targetDirectoryPathInModuleUploadDir
            ){

                $currentSubdirectoryPath = $currentDirectoryPathInModuleUploadDir;
                $targetSubdirectoryPath  = $targetDirectoryPathInModuleUploadDir;
            }elseif( $currentTargetDirectory === $currentDirectoryPathInModuleUploadDir ){ // current dir is main

                $currentSubdirectoryPath = $currentDirectoryPathInModuleUploadDir;
                $targetSubdirectoryPath  = $newTargetDirectory . DIRECTORY_SEPARATOR . $targetDirectoryPathInModuleUploadDir;
            }elseif( $newTargetDirectory === $targetDirectoryPathInModuleUploadDir ){ // target dir is main

                $currentSubdirectoryPath = $currentTargetDirectory . DIRECTORY_SEPARATOR . $currentDirectoryPathInModuleUploadDir;
                $targetSubdirectoryPath  = $targetDirectoryPathInModuleUploadDir;
            }

        }else { // there is NO main dir on any side
            $currentSubdirectoryPath = $currentTargetDirectory . DIRECTORY_SEPARATOR . $currentDirectoryPathInModuleUploadDir;
            $targetSubdirectoryPath  = $newTargetDirectory. DIRECTORY_SEPARATOR . $targetDirectoryPathInModuleUploadDir;
        }

        if( !file_exists($currentSubdirectoryPath) ){
            $logMessage        = $this->application->translator->translate('logs.files.currentSubdirectoryDoesNotExist');
            $responseMessage   = $this->application->translator->translate('responses.files.currentSubdirectoryDoesNotExist');
            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if( !file_exists($targetSubdirectoryPath) ){
            $logMessage        = $this->application->translator->translate('logs.files.targetSubdirectoryDoesNotExist');
            $responseMessage   = $this->application->translator->translate('responses.files.targetSubdirectoryDoesNotExist');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        try{
            Utils::copyFiles($currentSubdirectoryPath, $targetSubdirectoryPath, $this->fileTagger);
        }catch(Exception $e){
            $message = $this->application->translator->translate('logs.files.exceptionWasThrownWhileMovingDataBetweenFolders');
            $this->logger->info($message, [
                'message' => $e->getMessage()
            ]);

            $message = $this->application->translator->translate('responses.files.thereWasAnErrorWhileMovingDataBetweenFolders');
            return new Response($message,500);
        }

        $logMessage        = $this->application->translator->translate('logs.files.finishedCopyingData');
        $responseMessage   = $this->application->translator->translate('responses.files.finishedCopyingData');

        $this->logger->info($logMessage);
        return new Response($responseMessage, 200);
    }

    /**
     * @Route("/upload/action/copy-and-remove-folder-data", name="upload_copy_and_remove_folder_data", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function copyAndRemoveDataViaPost(Request $request) {

        if ( !$request->query->has(static::KEY_CURRENT_UPLOAD_MODULE_DIR) ) {
            $message = $this->application->translator->translate('responses.files.currentUploadTypeIsMissingInRequest');
            return new Response($message);
        }

        if ( !$request->query->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR) ) {
            $message = $this->application->translator->translate('responses.files.subdirectoryCurrentPathInModuleUploadDirIsMissingInRequest');
            return new Response($message);
        }

        $currentUploadModuleDir              = $request->query->get(static::KEY_CURRENT_UPLOAD_MODULE_DIR);
        $currentDirectoryPathInUploadTypeDir = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);

        try{
            $this->copyFolderDataToAnotherFolderByPostRequest($request);
            $this->directoriesHandle->removeFolder($currentUploadModuleDir, $currentDirectoryPathInUploadTypeDir);
        }catch(Exception $e){
            $message = $this->application->translator->translate('responses.files.thereWasAnErrorWhileCopyingAndRemovingDataViaPost');
            return new Response ($message);
        }

        $message = $this->application->translator->translate('responses.files.dataHasBeenSuccesfulyCopiedAndRemoved');
        return new Response($message);
    }


    /**
     * @param string $currentUploadType
     * @param string $targetUploadType
     * @param string $currentDirectoryPathInModuleUploadDir
     * @param string $targetDirectoryPathInModuleUploadDir
     * @return Response
     */
    public function copyData(
        ?string $currentUploadType,
        ?string $targetUploadType,
        ?string $currentDirectoryPathInModuleUploadDir,
        ?string $targetDirectoryPathInModuleUploadDir
    ) {

        try{
            $response = $this->copyFolderDataToAnotherFolder($currentUploadType, $targetUploadType, $currentDirectoryPathInModuleUploadDir, $targetDirectoryPathInModuleUploadDir);

            if( $response->getStatusCode() !== 200 ){
                $responseMessage = $response->getContent();
            }else{
                $responseMessage = $this->application->translator->translate('responses.files.finishedCopyingData');;
            }


        }catch(Exception $e){
            $logMessage        = $this->application->translator->translate('logs.files.exceptionWasThrownWhileMovingDataBetweenFolders');
            $responseMessage   = $this->application->translator->translate('responses.files.thereWasAnErrorWhileCopyingData');

            $this->logger->info($logMessage, [
                'message' => $e->getMessage()
            ]);
            return new Response ($responseMessage, 500);
        }

        $logMessage = $this->application->translator->translate('logs.files.finishedCopyingData');


        $this->logger->info($logMessage);
        return new Response($responseMessage, $response->getStatusCode());
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeFile(Request $request) {
        $isSingleFileRemove = true;

        if (
                !$request->request->has(static::KEY_FILE_FULL_PATH)
            &&  !$request->request->has(static::KEY_FILES_FULL_PATHS)
        ) {
            $message = $this->application->translator->translate('responses.general.missingRequiredParameter');
            $message .= static::KEY_FILE_FULL_PATH . ', ' . static::KEY_FILE_FULL_PATH;

            throw new Exception($message);
        }

        if( $request->request->has(static::KEY_FILE_FULL_PATH) ){
            $filepath = $request->request->get(static::KEY_FILE_FULL_PATH);

        }elseif($request->request->has(static::KEY_FILES_FULL_PATHS)){
            $isSingleFileRemove = false;
            $filepaths          = $request->request->get(static::KEY_FILES_FULL_PATHS);

            // Call Yourself for each filepath, this will fall into single filepath call
            foreach($filepaths as $filepath){
                $request = new Request();
                $request->request->set(static::KEY_FILE_FULL_PATH, $filepath);
                $this->removeFile($request);
            }

        }


        try{

            if( $isSingleFileRemove ){
                if( file_exists($filepath) ) {
                    unlink($filepath);

                    $this->fileTagger->prepare([], $filepath);
                    $this->fileTagger->updateTags();

                    $message = $this->application->translator->translate('responses.files.fileSuccessfullyRemoved');

                    return new Response($message, 200);
                }else{
                    $message = $this->application->translator->translate('responses.files.fileDoesNotExist');
                    return new Response($message, 404);
                }
            }else{
                $message = $this->application->translator->translate('responses.files.fileSuccessfullyRemoved');
                return new Response($message, 200);
            }

        }catch(Exception $e){
            $message = $this->application->translator->translate('responses.files.thereWasAnErrorWhileRemovingFile');
            return new Response($message, 500);
        }

    }

    /**
     * @param Request $request
     * @param callable $callback
     * @return JsonResponse
     * @throws Exception
     */
    public function renameFileViaRequest(Request $request, callable $callback = null): JsonResponse {

        if (!$request->request->has(static::KEY_FILE_FULL_PATH)) {
            $message   = $this->application->translator->translate('responses.general.missingRequiredParameter') . static::KEY_FILE_FULL_PATH;
            throw new Exception($message);
        }

        if (!$request->request->has(static::KEY_FILE_NEW_NAME)) {
            $message   = $this->application->translator->translate('responses.general.missingRequiredParameter') . static::KEY_FILE_NEW_NAME;
            throw new Exception($message);
        }

        $currRelativeFilepath     = $request->request->get(static::KEY_FILE_FULL_PATH);
        $currRelativeDirpath      = pathinfo($currRelativeFilepath, PATHINFO_DIRNAME);
        $currFileExtension        = pathinfo($currRelativeFilepath, PATHINFO_EXTENSION);

        $newFilename              = pathinfo(trim($request->request->get(static::KEY_FILE_NEW_NAME)),PATHINFO_FILENAME);
        $newFilenameWithExtension = static::buildFilenameWithExtension($newFilename, $currFileExtension);
        $newRelativeFilePath      = static::buildFileFullPathFromDirLocationAndFileName($currRelativeDirpath, $newFilenameWithExtension);

        $response = $this->renameFile($currRelativeFilepath, $newRelativeFilePath);

        if( is_callable($callback) ){
            $callback($currRelativeFilepath, $newRelativeFilePath);
        }

        return $response;
    }

    /**
     * @param string $currRelativeFilepath
     * @param string $newRelativeFilePath
     * @return JsonResponse
     */
    public function renameFile(string $currRelativeFilepath, string $newRelativeFilePath): JsonResponse {

        if( $newRelativeFilePath === $currRelativeFilepath){
            $message   = $this->application->translator->translate('responses.files.filenameRemainsTheSame');
            return new JsonResponse($message, 200);
        }

        $newFilename = pathinfo($newRelativeFilePath, PATHINFO_FILENAME);
        if( empty($newFilename) ){
            $message = $this->application->translator->translate('responses.files.filenameCannotBeEmpty');
            return new JsonResponse($message, 500);
        }

        try{

            if( !file_exists($newRelativeFilePath) ) {
                rename($currRelativeFilepath, $newRelativeFilePath);
                $this->imageHandler->moveMiniatureBasedOnMovingOriginalFile($currRelativeFilepath, $newRelativeFilePath);

                $message = $this->application->translator->translate('responses.files.fileSuccessfullyRename');
                return new JsonResponse($message, 200);
            }else{
                $message = $this->application->translator->translate('responses.files.fileWithThisNameAlreadyExist');
                return new JsonResponse($message, 500);
            }

        }catch(Exception $e){
            $this->application->logger->critical("Exception was thrown while renaming file.", [
                "message" => $e->getMessage(),
                "code"    => $e->getCode(),
                "class"   => __CLASS__,
                "method"  => __FUNCTION__,
            ]);

            $message = $this->application->translator->translate('responses.files.thereWasAnErrorWhileRenamingFile');
            return new JsonResponse($message, 500);
        }

    }

    public function moveSingleFile(string $currentFileLocation, string $targetFileLocation) {

        if( !file_exists($currentFileLocation) ){
            $message = $this->application->translator->translate('responses.files.fileYouTryingToMoveDoesNotExist');
            return new JsonResponse($message, 500);
        }

        if( file_exists($targetFileLocation) ){
            $message = $this->application->translator->translate('responses.files.fileWithThisNameAlreadyExistInTargetDirectory');
            return new JsonResponse($message, 500);
        }

        try{
            Utils::copyFiles($currentFileLocation, $targetFileLocation, $this->fileTagger);
            unlink($currentFileLocation);

            $this->fileTagger->updateFilePath($currentFileLocation, $targetFileLocation);
            $this->application->repositories->lockedResourceRepository->updatePath($currentFileLocation, $targetFileLocation);
            $this->imageHandler->moveMiniatureBasedOnMovingOriginalFile($currentFileLocation, $targetFileLocation);

            $message = $this->application->translator->translate('responses.files.fileHasBeenSuccesfullyMoved');
            return new JsonResponse($message, 200);
        }catch(Exception $e){
            $logMessage      = $this->application->translator->translate('logs.files.thereWasAnErrorWhileTryingToMoveSingleFile') . $e->getMessage();
            $responseMessage = $this->application->translator->translate('responses.files.couldNotMoveTheFile');

            $this->logger->critical($logMessage);
            return new JsonResponse($responseMessage, 500);
        }

    }

    /**
     * Will list all files in given directories
     *
     * @param array $directories
     * @return array
     */
    public function listAllFilesInDirectories(array $directories): array
    {
        $filesPathsList = [];

        foreach($directories as $directory){
            $finder = new Finder();
            $finder->depth(0);
            $finder->files()->in($directory);

            foreach($finder as $file){
                $filesPathsList[$directory][] = $file->getFilename();
            }
        }

        return $filesPathsList;
    }

    /**
     * Builds file full path from directory path and filename
     * @param string $dirPath
     * @param string $filename
     * @return string
     */
    public static function buildFileFullPathFromDirLocationAndFileName(string $dirPath, string $filename): string {

        $trimmedDirPath = static::trimFirstAndLastSlash($dirPath);
        $fileFullPath   = $trimmedDirPath . DIRECTORY_SEPARATOR . $filename;

        return $fileFullPath;
    }

    public static function buildFilenameWithExtension(string $filename, string $extension): string {
        $filenameWithExtension = $filename . DOT . $extension;
        return $filenameWithExtension;
    }

    /**
     * Removes first and last slash from $dirPath
     * @param string $dirPath
     * @return bool|string
     */
    public static function trimFirstAndLastSlash(string $dirPath) {
        $trimmedDirPath = $dirPath;

        $isLeadingSlash  = ( substr($dirPath, 0, 1) === DIRECTORY_SEPARATOR );
        $isLastSlash     = ( substr($dirPath, -1) === DIRECTORY_SEPARATOR );

        if( $isLeadingSlash ){
            $trimmedDirPath = substr($trimmedDirPath, 1);
        }

        if( $isLastSlash ){
            $trimmedDirPath = substr($trimmedDirPath, 0, -1);
        }

        return $trimmedDirPath;
    }

    /**
     * @param string $dirPath
     * @return int
     */
    public static function countFilesInTree(string $dirPath) {

        $finder = new Finder();
        $finder->files()->in($dirPath);
        $filesCountInTree = count($finder);

        return $filesCountInTree;
    }

    /**
     * This function will return file path with leading slash if such is missing
     * @param string $filePath
     * @param bool $skipAddingForLinks
     * @return string
     */
    public static function addTrailingSlashIfMissing(string $filePath, $skipAddingForLinks = false): string{

        $isFilePathWithoutTrailingSlash = ( 0 !== strpos($filePath, DIRECTORY_SEPARATOR) );

        $isSkipped = false;
        $matchesToSkipLinks = [
          "www",
          "http"
        ];

        if( $isFilePathWithoutTrailingSlash ){

            foreach( $matchesToSkipLinks as $singleMatch ){
                if( strstr($filePath, $singleMatch) ) {
                    $isSkipped = true;
                    break;
                }
            }

            if( !$isSkipped ){
                $filePath = DIRECTORY_SEPARATOR . $filePath;
            }
        }

        return $filePath;
    }

    /**
     * This function returns the full path excluding the base `upload/images/ or upload/files/`
     * @param string $fullPath
     * @param string $uploadModuleFolder
     * @return string
     */
    public static function getSubdirectoryPathFromUploadModuleUploadFullPath(string $fullPath, string $uploadModuleFolder): string
    {
        $strippedFolderPath = FilesHandler::trimFirstAndLastSlash($fullPath);

        $regexReplace = "#upload[\/]?{$uploadModuleFolder}[\/]?#";
        $folder       = preg_replace($regexReplace, "", $strippedFolderPath);

        return $folder;
    }

    /**
     * This function returns the target upload dir for module, example (files, images)
     * @param string $path
     * @return string
     */
    public static function getModuleUploadDirForUploadPath(string $path)
    {
        $path = FilesHandler::trimFirstAndLastSlash($path);

        if( strstr($path, self::KEY_UPLOAD_DIR) ){
            $path = str_replace(self::KEY_UPLOAD_DIR . DIRECTORY_SEPARATOR, "", $path);
        }

        preg_match("#^(.*)(\/)#", $path, $matches);

        if( array_key_exists(1, $matches) ){
           return $matches[1];
        }

        return $path;
    }

}