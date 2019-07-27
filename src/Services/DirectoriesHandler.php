<?php

namespace App\Services;

use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Utils;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This service is responsible for handling folders in terms of internal usage, like moving/renaming/etc...
 * Class DirectoriesHandler
 * @package App\Services
 */
class DirectoriesHandler {

    const SUBDIRECTORY_KEY = 'subdirectory';

    /**
     * @var Application $application
     */
    private $application;

    /**
     * @var Logger $logger
     */
    private $logger;

    public function __construct(Application $application, Logger $logger) {
        $this->application = $application;
        $this->logger      = $logger;
    }


    /**
     * @Route("/upload/{upload_type}/remove-subdirectory", name="upload_remove_subdirectory", methods="POST")
     * @param string $upload_type
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function removeFolderByPostRequest(string $upload_type, Request $request){

        if ( !$request->query->has(FileUploadController::KEY_SUBDIRECTORY_NAME) ) {
            return new Response("Subdirectory name is missing in request.");
        }

        $subdirectory_name  = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_NAME);
        $response           = $this->removeFolder($upload_type, $subdirectory_name);

        return $response;
    }


    /**
     * @param string $upload_type
     * @param string $subdirectory_name
     * @return Response
     * @throws \Exception
     */
    public function removeFolder(string $upload_type, string $subdirectory_name){

        $this->logger->info('Started removing folder: ', [
            'upload_type'       => $upload_type,
            'subdirectory_name' => $subdirectory_name
        ]);

        $target_directory           = FileUploadController::getTargetDirectoryForUploadType($upload_type);

        $is_subdirectory_existing   = !FileUploadController::isSubdirectoryForTypeExisting($target_directory, $subdirectory_name);

        if( $is_subdirectory_existing ){
            $this->logger->info('Removed folder does not exists - removal aborted');
            return new Response('This subdirectory does not exist for current upload type.');
        }

        $subdirectory_path = FileUploadController::getSubdirectoryPath($target_directory, $subdirectory_name);

        try{
            Utils::removeFolderRecursively($subdirectory_path);
        }catch(\Exception $e){
            $this->logger->info('Could not remove folder: ', [
                'message' => $e->getMessage()
            ]);
            return new Response('There was and error when trying to remove subdirectory!');
        }

        $this->logger->info('Finished removing folder.');
        return new Response('Subdirectory has been successfully removed.');

    }

    /**
     * @Route("/upload/{upload_type}/rename-subdirectory", name="upload_rename_subdirectory", methods="POST")
     * @param string $upload_type
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function renameSubdirectoryByPostRequest(string $upload_type, Request $request) {

        if ( !$request->query->has(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME) ) {
            return new Response("Subdirectory new name is missing in request.");
        }

        if ( !$request->query->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_NAME) ) {
            return new Response("Subdirectory current name is missing in request.");
        }

        $subdirectory_current_name  = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_NAME);
        $subdirectory_new_name      = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME);

        $response = $this->renameSubdirectory($upload_type, $subdirectory_current_name, $subdirectory_new_name);

        return $response;
    }

    /**
     * @param string $upload_type
     * @param string $subdirectory_current_name
     * @param string $subdirectory_new_name
     * @return Response
     * @throws \Exception
     */
    public function renameSubdirectory(string $upload_type, string $subdirectory_current_name, string $subdirectory_new_name) {

        $this->logger->info('Started renaming subdirectory: ', [
            'upload_type'               => $upload_type,
            'subdirectory_current_name' => $subdirectory_current_name,
            'subdirectory_new_name'     => $subdirectory_new_name
        ]);

        if( $subdirectory_current_name === $subdirectory_new_name ){
            $this->logger->info('Subdirectory name will not change - renaming aborted.');
            return new Response("You are trying to change folder name to the same that there already is - action aborted.", 500);
        }

        if ( empty($subdirectory_new_name) ){
            $this->logger->info('Subdirectory name is an empty string - renaming aborted.');
            return new Response('New name is an empty string - action aborted', 500);
        }

        $target_directory       = FileUploadController::getTargetDirectoryForUploadType($upload_type);
        $subdirectory_exists    = FileUploadController::isSubdirectoryForTypeExisting($target_directory, $subdirectory_current_name);

        if( !$subdirectory_exists ){
            $message = "Subdirectory with this name does not exist!";
            $this->logger->info($message);
            return new Response($message, 500);
        }

        $subdirectory_with_new_name_exists = FileUploadController::isSubdirectoryForTypeExisting($target_directory, $subdirectory_new_name);

        if( $subdirectory_with_new_name_exists ){
            $this->logger->info('Subdirectory with this name already exists - renaming aborted.');
            return new Response(" Cannot change subdirectory name! Subdirectory with this name already exist.", 500);
        }

        try{
            $old_folder_location = FileUploadController::getSubdirectoryPath($target_directory, $subdirectory_current_name);
            $new_folder_location = FileUploadController::getSubdirectoryPath($target_directory, $subdirectory_new_name);
            rename($old_folder_location, $new_folder_location);
        }catch(\Exception $e){
            $this->logger->info('Exception was thrown while renaming folder: ', [
                'message' => $e->getMessage()
            ]);

            return new Response('There was an error when renaming the folder! Error message. Most likely due to unallowed characters used in name.', 500);
        }

        $this->logger->info('Finished renaming subdirectory.');
        return new Response('Folder name has been successfully changed', 200);

    }

    /**
     * @param string $upload_type
     * @param string $subdirectory_name
     * @return Response
     * @throws \Exception
     */
    public function createFolder(string $upload_type, string $subdirectory_name){

        $this->logger->info('Started creating subdirectory: ', [
            'upload_type'       => $upload_type,
            'subdirectory_name' => $subdirectory_name
        ]);

        $target_directory       = FileUploadController::getTargetDirectoryForUploadType($upload_type);
        $full_subdir_path       = FileUploadController::getSubdirectoryPath($target_directory, $subdirectory_name);

        if( file_exists($full_subdir_path) ){
            $this->logger->info('Subdirectory with this name already exists.');
            return new Response('Subdirectory with this name for selected upload typ already exists.', 500);
        }

        try {
            mkdir($full_subdir_path, 0777);
        } catch (\Exception $e) {
            $this->logger->info('Exception was thrown while creating folder: ', [
                'message' => $e->getMessage()
            ]);

            return new Response('There was an error while trying to create new folder for given upload type', 500);
        }

        $this->logger->info('Finished creating subdirectory.');
        return new Response ('Subdirectory for selected upload_type has been successfully created', 200);
    }

}