<?php

namespace App\Services;

use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Utils;
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
    const KEY_FILE_NEW_NAME             = 'file_new_name';
    const KEY_FILE_CURRENT_PATH         = 'file_current_location';
    const KEY_FILE_NEW_PATH             = 'file_new_location';
    const KEY_MODULES_NAMES             = 'modules_names';

    const FILE_KEY                      = 'file';

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

    public function __construct(Application $application, DirectoriesHandler $directories_handler, LoggerInterface $logger) {
        $this->application          = $application;
        $this->directories_handle   = $directories_handler;
        $this->logger               = $logger;

    }

    /**
     * @Route("/upload/action/copy-folder-data", name="upload_copy_folder_data", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
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
     * @throws \Exception
     */
    public function copyFolderDataToAnotherFolder(
        ?string $current_upload_type,
        ?string $target_upload_type,
        ?string $current_directory_path_in_module_upload_dir,
        ?string $target_directory_path_in_module_upload_dir
    ){
        $current_subdirectory_name = basename($current_directory_path_in_module_upload_dir);
        $target_subdirectory_name  = basename($target_directory_path_in_module_upload_dir);

        $this->logger->info('Started copying data between folders via Post Request.', [
            'current_upload_type'          => $current_upload_type,
            'target_upload_type'           => $target_upload_type,
            'current_subdirectory_name'    => $current_subdirectory_name,
            'target_subdirectory_name'     => $target_subdirectory_name,
            'current_directory_path_in_module_upload_dir' => $current_directory_path_in_module_upload_dir,
            'target_directory_path_in_module_upload_dir'  => $target_directory_path_in_module_upload_dir,
        ]);

        if ( empty($current_upload_type) ) {
            return new Response("Current upload type is missing in request.", 500);
        }

        if ( empty($target_upload_type) ) {
            return new Response("Target upload type is missing in request.", 500);
        }

        if ( empty($current_directory_path_in_module_upload_dir) ) {
            return new Response("Current subdirectory path in module upload dir is missing in request.", 500);
        }

        if ( empty($target_directory_path_in_module_upload_dir) ) {
            return new Response("Target subdirectory path in module upload dir is missing in request.", 500);
        }

        if(
                ( $current_upload_type === $target_upload_type )
            &&  ( $current_subdirectory_name === $target_subdirectory_name )
        ){
            return new Response("Cannot copy data to the same folder for given module.", 500);
        }

        $current_target_directory = FileUploadController::getTargetDirectoryForUploadModuleDir($current_upload_type);
        $new_target_directory     = FileUploadController::getTargetDirectoryForUploadModuleDir($target_upload_type);

        # checking if it's not main dir
        if( $current_target_directory !== $current_directory_path_in_module_upload_dir ){

            $current_subdirectory_path = $current_target_directory . '/' . $current_directory_path_in_module_upload_dir;
            $target_subdirectory_path  = $new_target_directory. '/' . $target_directory_path_in_module_upload_dir;

            if( !file_exists($current_subdirectory_path) ){
                $message = 'Current subdirectory does not exist.';
                $this->logger->info($message);
                return new Response($message, 500);
            }

            if( !file_exists($target_subdirectory_path) ){
                $message = 'Target subdirectory does not exist.';
                $this->logger->info($message);
                return new Response($message, 500);
            }

        }else{
            $current_subdirectory_path = $current_directory_path_in_module_upload_dir;
            $target_subdirectory_path  = $target_directory_path_in_module_upload_dir;
        }

        try{
            Utils::copyFiles($current_subdirectory_path, $target_subdirectory_path);
        }catch(\Exception $e){
            $this->logger->info('Exception was thrown while moving data between folders', [
                'message' => $e->getMessage()
            ]);

            return new Response('There was an error while moving files from one folder to another.',500);
        }

        $this->logger->info('Finished copying data.');
        return new Response('Data has been successfully moved to new directory', 200);
    }

    /**
     * @Route("/upload/action/copy-and-remove-folder-data", name="upload_copy_and_remove_folder_data", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function copyAndRemoveDataViaPost(Request $request) {

        if ( !$request->query->has(static::KEY_CURRENT_UPLOAD_MODULE_DIR) ) {
            return new Response("Current upload type is missing in request.");
        }

        if ( !$request->query->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR) ) {
            return new Response("Subdirectory current path in module upload dir is missing in request.");
        }

        $current_upload_module_dir                  = $request->query->get(static::KEY_CURRENT_UPLOAD_MODULE_DIR);
        $current_directory_path_in_upload_type_dir  = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);

        try{
            $this->copyFolderDataToAnotherFolderByPostRequest($request);
            $this->directories_handle->removeFolder($current_upload_module_dir, $current_directory_path_in_upload_type_dir);
        }catch(\Exception $e){
            return new Response ('Then was an error while copying and removing data.');
        }

        return new Response('Data has been successfully copied and removed afterward.');
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
                $response_message   = $response->getContent();
            }else{
                $response_message   = 'Data has been successfully copied.';
            }

            $this->logger->info('Started removing folder data.');

            $log_message = 'Copying data has been finished!';

        }catch(\Exception $e){
            $this->logger->info('Exception was thrown while trying to copy and remove data: ', [
                'message' => $e->getMessage()
            ]);
            return new Response ('Then was an error while copying and removing data.', 500);
        }

        $this->logger->info($log_message);
        return new Response($response_message, $response->getStatusCode());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function removeFile(Request $request) {

        if (!$request->request->has(static::KEY_FILE_FULL_PATH)) {
            throw new \Exception('Missing request parameter named: ' . static::KEY_FILE_FULL_PATH);
        }

        $filepath = $request->request->get(static::KEY_FILE_FULL_PATH);

        try{

            $file_location = $_SERVER['DOCUMENT_ROOT'] . $filepath;

            if( file_exists($file_location) ) {
                unlink($file_location);
                return new JsonResponse('File has been successfully removed.', 200);
            }else{
                return new JsonResponse('File does not exist.', 404);
            }

        }catch(\Exception $e){
            return new JsonResponse('There was an error while removing the file.', 500);
        }

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function renameFile(Request $request) {

        if (!$request->request->has(static::KEY_FILE_FULL_PATH)) {
            throw new \Exception('Missing request parameter named: ' . static::KEY_FILE_FULL_PATH);
        }

        if (!$request->request->has(static::KEY_FILE_NEW_NAME)) {
            throw new \Exception('Missing request parameter named: ' . static::KEY_FILE_NEW_NAME);
        }

        $filepath               = $_SERVER['DOCUMENT_ROOT'].$request->request->get(static::KEY_FILE_FULL_PATH);
        $filename               = basename($filepath);
        $curr_file_extension    = pathinfo($filename, PATHINFO_EXTENSION);

        $filedir                = str_replace($filename, '', $filepath);
        $new_filename           = $request->request->get(static::KEY_FILE_NEW_NAME);
        $new_file_extension     = pathinfo($new_filename, PATHINFO_EXTENSION);

        $new_file_path          = $filedir.trim($new_filename);

        if( $curr_file_extension !== $new_file_extension ){
            $new_file_path .= '.' . $curr_file_extension;
        }

        if( empty($new_filename) ){
            return new JsonResponse('File name cannot be empty!', 500);
        }

        try{

            if( $filepath === $new_file_path){
                return new JsonResponse('File name remains the same.', 200);
            }

            if( !file_exists($new_file_path) ) {
                rename($filepath, $new_file_path);
                return new JsonResponse('File has been successfully renamed.', 200);
            }else{
                return new JsonResponse('File with this name already exist.', 500);
            }

        }catch(\Exception $e){
            return new JsonResponse('There was an error while renaming the file.', 500);
        }

    }

    public function moveSingleFile(string $current_file_location, string $target_file_location) {

        if( !file_exists($current_file_location) ){
            return new JsonResponse('The file You trying to move does not exist.', 500);
        }

        if( file_exists($target_file_location) ){
            return new JsonResponse('File with this name already exists in target directory.', 500);
        }

        try{
            Utils::copyFiles($current_file_location, $target_file_location);
            unlink($current_file_location);
            return new JsonResponse('File has been successfully moved', 200);
        }catch(\Exception $e){
            $this->logger->critical("There was an error while trying to move single file {$e->getMessage()}");
            return new JsonResponse("Could not move the file.", 500);
        }

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

}