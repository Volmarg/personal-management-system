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

/**
 * This service is responsible for handling files in terms of internal usage, like moving/renaming/etc...
 * Class FilesHandler
 * @package App\Services
 */

class FilesHandler {

    const KEY_CURRENT_UPLOAD_MODULE_DIR = 'current_upload_module_dir';
    const KEY_TARGET_MODULE_UPLOAD_DIR  = 'target_upload_module_dir';
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
     * @var DirectoriesHandler $directories_handle
     */
    private $directories_handle;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var FileTagger $file_tagger
     */
    private $file_tagger;

    public function __construct(Application $application, DirectoriesHandler $directories_handler, LoggerInterface $logger, FileTagger $file_tagger) {
        $this->application          = $application;
        $this->directories_handle   = $directories_handler;
        $this->logger               = $logger;
        $this->file_tagger          = $file_tagger;
    }

    /**
     * @Route("/upload/action/copy-folder-data", name="upload_copy_folder_data", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function copyFolderDataToAnotherFolderByPostRequest(Request $request) {

        $current_upload_module_dir  = $request->query->get(static::KEY_CURRENT_UPLOAD_MODULE_DIR);
        $target_upload_module_dir   = $request->query->get(static::KEY_TARGET_MODULE_UPLOAD_DIR);
        $current_directory_path_in_module_upload_dir  = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);
        $target_directory_path_in_module_upload_dir   = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);

        $response = $this->copyFolderDataToAnotherFolder(
            $current_upload_module_dir,
            $target_upload_module_dir,
            $current_directory_path_in_module_upload_dir,
            $target_directory_path_in_module_upload_dir
        );

        return $response;
    }

    /**
     * @param string $current_upload_type
     * @param string $target_upload_type
     * @param string $current_directory_path_in_module_upload_dir
     * @param string $target_directory_path_in_module_upload_dir
     * @return Response
     * @throws Exception
     */
    public function copyFolderDataToAnotherFolder(
        ?string $current_upload_type,
        ?string $target_upload_type,
        ?string $current_directory_path_in_module_upload_dir,
        ?string $target_directory_path_in_module_upload_dir
    ){
        $current_subdirectory_name = basename($current_directory_path_in_module_upload_dir);
        $target_subdirectory_name  = basename($target_directory_path_in_module_upload_dir);

        $message = $this->application->translator->translate('logs.files.startedCopyingDataBetweenFoldersViaPost');

        $this->logger->info($message, [
            'current_upload_type'          => $current_upload_type,
            'target_upload_type'           => $target_upload_type,
            'current_subdirectory_name'    => $current_subdirectory_name,
            'target_subdirectory_name'     => $target_subdirectory_name,
            'current_directory_path_in_module_upload_dir' => $current_directory_path_in_module_upload_dir,
            'target_directory_path_in_module_upload_dir'  => $target_directory_path_in_module_upload_dir,
        ]);

        if ( empty($current_upload_type) ) {
            $message = $this->application->translator->translate('responses.files.currentUploadTypeIsMissingInRequest');
            return new Response($message, 500);
        }

        if ( empty($target_upload_type) ) {
            $message = $this->application->translator->translate('responses.files.targetUploadTypeIsMissingInRequest');
            return new Response($message, 500);
        }

        if ( empty($current_directory_path_in_module_upload_dir) ) {
            $message = $this->application->translator->translate('responses.files.currentSubdirectoryPathIsMissingInRequest');
            return new Response($message, 500);
        }

        if ( empty($target_directory_path_in_module_upload_dir) ) {
            $message = $this->application->translator->translate('responses.files.targetSubdirectoryPathIsMissingInRequest');
            return new Response($message, 500);
        }

        if(
                ( $current_upload_type === $target_upload_type )
            &&  ( $current_subdirectory_name === $target_subdirectory_name )
        ){
            $message = $this->application->translator->translate('responses.files.cannotCopyDataToTheSameFolderForGivenModule');
            return new Response($message. 500);
        }

        $current_target_directory = FileUploadController::getTargetDirectoryForUploadModuleDir($current_upload_type);
        $new_target_directory     = FileUploadController::getTargetDirectoryForUploadModuleDir($target_upload_type);

        # checking if it's not main dir on any side
        if( $current_target_directory === $current_directory_path_in_module_upload_dir ){ // current dir is main

            $current_subdirectory_path = $current_directory_path_in_module_upload_dir;
            $target_subdirectory_path  = $new_target_directory . DIRECTORY_SEPARATOR . $target_directory_path_in_module_upload_dir;

        }elseif( $new_target_directory === $target_directory_path_in_module_upload_dir ){ // target dir is main

            $current_subdirectory_path = $current_target_directory . DIRECTORY_SEPARATOR . $current_directory_path_in_module_upload_dir;
            $target_subdirectory_path  = $target_directory_path_in_module_upload_dir;

        } else { // there is NO main dir on any side

            $current_subdirectory_path = $current_target_directory . DIRECTORY_SEPARATOR . $current_directory_path_in_module_upload_dir;
            $target_subdirectory_path  = $new_target_directory. DIRECTORY_SEPARATOR . $target_directory_path_in_module_upload_dir;

        }

        if( !file_exists($current_subdirectory_path) ){
            $log_message        = $this->application->translator->translate('logs.files.currentSubdirectoryDoesNotExist');
            $response_message   = $this->application->translator->translate('responses.files.currentSubdirectoryDoesNotExist');
            $this->logger->info($log_message);
            return new Response($response_message, 500);
        }

        if( !file_exists($target_subdirectory_path) ){
            $log_message        = $this->application->translator->translate('logs.files.targetSubdirectoryDoesNotExist');
            $response_message   = $this->application->translator->translate('responses.files.targetSubdirectoryDoesNotExist');

            $this->logger->info($log_message);
            return new Response($response_message, 500);
        }

        try{
            Utils::copyFiles($current_subdirectory_path, $target_subdirectory_path, $this->file_tagger);
        }catch(Exception $e){
            $message = $this->application->translator->translate('logs.files.exceptionWasThrownWhileMovingDataBetweenFolders');
            $this->logger->info($message, [
                'message' => $e->getMessage()
            ]);

            $message = $this->application->translator->translate('responses.files.thereWasAnErrorWhileMovingDataBetweenFolders');
            return new Response($message,500);
        }

        $log_message        = $this->application->translator->translate('logs.files.finishedCopyingData');
        $response_message   = $this->application->translator->translate('responses.files.finishedCopyingData');

        $this->logger->info($log_message);
        return new Response($response_message, 200);
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

        $current_upload_module_dir                  = $request->query->get(static::KEY_CURRENT_UPLOAD_MODULE_DIR);
        $current_directory_path_in_upload_type_dir  = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);

        try{
            $this->copyFolderDataToAnotherFolderByPostRequest($request);
            $this->directories_handle->removeFolder($current_upload_module_dir, $current_directory_path_in_upload_type_dir);
        }catch(Exception $e){
            $message = $this->application->translator->translate('responses.files.thereWasAnErrorWhileCopyingAndRemovingDataViaPost');
            return new Response ($message);
        }

        $message = $this->application->translator->translate('responses.files.dataHasBeenSuccesfulyCopiedAndRemoved');
        return new Response($message);
    }


    /**
     * @param string $current_upload_type
     * @param string $target_upload_type
     * @param string $current_directory_path_in_module_upload_dir
     * @param string $target_directory_path_in_module_upload_dir
     * @return Response
     */
    public function copyData(
        ?string $current_upload_type,
        ?string $target_upload_type,
        ?string $current_directory_path_in_module_upload_dir,
        ?string $target_directory_path_in_module_upload_dir
    ) {

        try{
            $response = $this->copyFolderDataToAnotherFolder($current_upload_type, $target_upload_type, $current_directory_path_in_module_upload_dir, $target_directory_path_in_module_upload_dir);

            if( $response->getStatusCode() !== 200 ){
                $response_message = $response->getContent();
            }else{
                $response_message = $this->application->translator->translate('responses.files.finishedCopyingData');;
            }


        }catch(Exception $e){
            $log_message        = $this->application->translator->translate('logs.files.exceptionWasThrownWhileMovingDataBetweenFolders');
            $response_message   = $this->application->translator->translate('responses.files.thereWasAnErrorWhileCopyingData');

            $this->logger->info($log_message, [
                'message' => $e->getMessage()
            ]);
            return new Response ($response_message, 500);
        }

        $log_message = $this->application->translator->translate('logs.files.finishedCopyingData');


        $this->logger->info($log_message);
        return new Response($response_message, $response->getStatusCode());
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeFile(Request $request) {
        $is_single_file_remove = true;

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
            $is_single_file_remove = false;
            $filepaths             = $request->request->get(static::KEY_FILES_FULL_PATHS);

            // Call Yourself for each filepath, this will fall into single filepath call
            foreach($filepaths as $filepath){
                $request = new Request();
                $request->request->set(static::KEY_FILE_FULL_PATH, $filepath);
                $this->removeFile($request);
            }

        }


        try{

            if( $is_single_file_remove ){
                if( file_exists($filepath) ) {
                    unlink($filepath);

                    $this->file_tagger->prepare([], $filepath);
                    $this->file_tagger->updateTags();

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

        $curr_relative_filepath     = $request->request->get(static::KEY_FILE_FULL_PATH);
        $curr_relative_dirpath      = pathinfo($curr_relative_filepath, PATHINFO_DIRNAME);
        $curr_file_extension        = pathinfo($curr_relative_filepath, PATHINFO_EXTENSION);

        $new_filename                = pathinfo(trim($request->request->get(static::KEY_FILE_NEW_NAME)),PATHINFO_FILENAME);
        $new_filename_with_extension = static::buildFilenameWithExtension($new_filename, $curr_file_extension);
        $new_relative_file_path      = static::buildFileFullPathFromDirLocationAndFileName($curr_relative_dirpath, $new_filename_with_extension);

        $response = $this->renameFile($curr_relative_filepath, $new_relative_file_path);

        if( is_callable($callback) ){
            $callback($curr_relative_filepath, $new_relative_file_path);
        }

        return $response;
    }

    /**
     * @param string $curr_relative_filepath
     * @param string $new_relative_file_path
     * @return JsonResponse
     */
    public function renameFile(string $curr_relative_filepath, string $new_relative_file_path): JsonResponse {

        if( $new_relative_file_path === $curr_relative_filepath){
            $message   = $this->application->translator->translate('responses.files.filenameRemainsTheSame');
            return new JsonResponse($message, 200);
        }

        $new_filename = pathinfo($new_relative_file_path, PATHINFO_FILENAME);

        if( empty($new_filename) ){
            $message   = $this->application->translator->translate('responses.files.filenameCannotBeEmpty');
            return new JsonResponse($message, 500);
        }

        try{

            if( !file_exists($new_relative_file_path) ) {
                rename($curr_relative_filepath, $new_relative_file_path);

                $message = $this->application->translator->translate('responses.files.fileSuccessfullyRename');
                return new JsonResponse($message, 200);
            }else{
                $message = $this->application->translator->translate('responses.files.fileWithThisNameAlreadyExist');
                return new JsonResponse($message, 500);
            }

        }catch(Exception $e){
            $message = $this->application->translator->translate('responses.files.thereWasAnErrorWhileRenamingFile');
            return new JsonResponse($message, 500);
        }

    }

    public function moveSingleFile(string $current_file_location, string $target_file_location) {

        if( !file_exists($current_file_location) ){
            $message = $this->application->translator->translate('responses.files.fileYouTryingToMoveDoesNotExist');
            return new JsonResponse($message, 500);
        }

        if( file_exists($target_file_location) ){
            $message = $this->application->translator->translate('responses.files.fileWithThisNameAlreadyExistInTargetDirectory');
            return new JsonResponse($message, 500);
        }

        try{
            Utils::copyFiles($current_file_location, $target_file_location, $this->file_tagger);
            unlink($current_file_location);

            $this->file_tagger->updateFilePath($current_file_location, $target_file_location);
            $this->application->repositories->lockedResourceRepository->updatePath($current_file_location, $target_file_location);

            $message = $this->application->translator->translate('responses.files.fileHasBeenSuccesfullyMoved');
            return new JsonResponse($message, 200);
        }catch(Exception $e){
            $log_message      = $this->application->translator->translate('logs.files.thereWasAnErrorWhileTryingToMoveSingleFile') . $e->getMessage();
            $response_message = $this->application->translator->translate('responses.files.couldNotMoveTheFile');

            $this->logger->critical($log_message);
            return new JsonResponse($response_message, 500);
        }

    }

    /**
     * Builds file full path from directory path and filename
     * @param string $dir_path
     * @param string $filename
     * @return string
     */
    public static function buildFileFullPathFromDirLocationAndFileName(string $dir_path, string $filename): string {

        $trimmed_dir_path = static::trimFirstAndLastSlash($dir_path);
        $fileFullPath     = $trimmed_dir_path . DIRECTORY_SEPARATOR . $filename;

        return $fileFullPath;
    }

    public static function buildFilenameWithExtension(string $filename, string $extension): string {
        $filename_with_extension = $filename . DOT . $extension;
        return $filename_with_extension;
    }

    /**
     * Removes first and last slash from $dir_path
     * @param string $dir_path
     * @return bool|string
     */
    public static function trimFirstAndLastSlash(string $dir_path) {
        $trimmed_dir_path = $dir_path;

        $is_leading_slash  = ( substr($dir_path, 0, 1) === DIRECTORY_SEPARATOR );
        $is_last_slash     = ( substr($dir_path, -1) === DIRECTORY_SEPARATOR );

        if( $is_leading_slash ){
            $trimmed_dir_path = substr($trimmed_dir_path, 1);
        }

        if( $is_last_slash ){
            $trimmed_dir_path = substr($trimmed_dir_path, 0, -1);
        }

        return $trimmed_dir_path;
    }

    /**
     * @param string $dir_path
     * @return int
     */
    public static function countFilesInTree(string $dir_path) {

        $finder = new Finder();
        $finder->files()->in($dir_path);
        $files_count_in_tree = count($finder);

        return $files_count_in_tree;
    }

    /**
     * This function will return file path with leading slash if such is missing
     * @param string $file_path
     * @param bool $skip_adding_for_links
     * @return string
     */
    public static function addTrailingSlashIfMissing(string $file_path, $skip_adding_for_links = false): string{

        $is_file_path_without_trailing_slash = ( 0 !== strpos($file_path, DIRECTORY_SEPARATOR) );

        $is_skipped = false;
        $matches_to_skip_links = [
          "www",
          "http"
        ];

        if( $is_file_path_without_trailing_slash ){

            foreach( $matches_to_skip_links as $single_match ){
                if( strstr($file_path, $single_match) ) {
                    $is_skipped = true;
                    break;
                }
            }

            if( !$is_skipped ){
                $file_path = DIRECTORY_SEPARATOR . $file_path;
            }
        }

        return $file_path;
    }

    /**
     * This function returns the full path excluding the base `upload/images/ or upload/files/`
     * @param string $full_path
     * @param string $upload_module_folder
     * @return string
     */
    public static function getSubdirectoryPathFromUploadModuleUploadFullPath(string $full_path, string $upload_module_folder): string
    {
        $stripped_folder_path = FilesHandler::trimFirstAndLastSlash($full_path);

        $regex_replace = "#upload[\/]?{$upload_module_folder}[\/]?#";
        $folder        = preg_replace($regex_replace, "", $stripped_folder_path);

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