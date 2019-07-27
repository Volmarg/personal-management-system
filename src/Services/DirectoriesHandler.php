<?php

namespace App\Services;

use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Utils;
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

    public function __construct(Application $application) {
        $this->application = $application;
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

        $target_directory = FileUploadController::getTargetDirectoryForUploadType($upload_type);

        $is_subdirectory_existing = !FileUploadController::isSubdirectoryForTypeExisting($target_directory, $subdirectory_name);

        if( $is_subdirectory_existing ){
            return new Response('This subdirectory does not exist for current upload type.');
        }

        $subdirectory_path = FileUploadController::getSubdirectoryPath($target_directory, $subdirectory_name);

        try{
            Utils::removeFolderRecursively($subdirectory_path);
        }catch(\Exception $e){
            return new Response('There was and error when trying to remove subdirectory!');
        }

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

        if( $subdirectory_current_name === $subdirectory_new_name ){
            return new Response("You are trying to change folder name to the same that there already is - action aborted.");
        }

        if ( empty($subdirectory_new_name) ){
            return new Response('New name is an empty string - action aborted');
        }

        $target_directory       = FileUploadController::getTargetDirectoryForUploadType($upload_type);
        $subdirectory_exists    = FileUploadController::isSubdirectoryForTypeExisting($target_directory, $subdirectory_current_name);

        if( !$subdirectory_exists ){
            return new Response("Subdirectory with this name does not exist!");
        }

        $subdirectory_with_new_name_exists = FileUploadController::isSubdirectoryForTypeExisting($target_directory, $subdirectory_new_name);

        if( $subdirectory_with_new_name_exists ){
            return new Response(" Cannot change folder name! Folder with this name already exist.");
        }

        try{
            $old_folder_location = $target_directory.'/'.$subdirectory_current_name;
            $new_folder_location = $target_directory.'/'.$subdirectory_new_name;
            rename($old_folder_location, $new_folder_location);
        }catch(\Exception $e){
            return new Response('There was an error when renaming the folder! Error message. Most likely due to unallowed characters used in name.');
        }

        return new Response('Folder name has been successfully changed');

    }


}