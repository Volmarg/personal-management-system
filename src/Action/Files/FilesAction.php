<?php

namespace App\Action\Files;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Env;
use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Utils;
use App\Form\Files\UploadSubdirectoryCopyDataType;
use App\Form\Files\UploadSubdirectoryCreateType;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;

class FilesAction extends AbstractController {

    const KEY_RESPONSE_CODE         = 'response_code';
    const KEY_RESPONSE_MESSAGE      = 'response_message';
    const KEY_RESPONSE_DATA         = 'response_data';
    const KEY_RESPONSE_ERRORS_DATA  = 'response_errors_data';

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var FilesHandler $files_handler
     */
    private $files_handler;

    /**
     * @var FileTagger $file_tagger
     */
    private $file_tagger;

    /**
     * @var DirectoriesHandler $directories_handler
     */
    private $directories_handler;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(
        Application        $app,
        FilesHandler       $files_handler,
        FileTagger         $file_tagger,
        DirectoriesHandler $directories_handler,
        Controllers        $controllers
    ) {
        $this->app                 = $app;
        $this->files_handler       = $files_handler;
        $this->file_tagger         = $file_tagger;
        $this->directories_handler = $directories_handler;
        $this->controllers         = $controllers;
    }

    /**
     * @Route("/files/action/remove-file", name="files_remove_file", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function removeFileViaPost(Request $request) {
        $response = $this->files_handler->removeFile($request);

        $code    = $response->getStatusCode();
        $message = $response->getContent();

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
    }

    /**
     * @Route("/files/action/rename-file", name="files_rename_file", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function renameFileViaPost(Request $request) {

        $update_file_path = function ($curr_relative_filepath, $new_relative_file_path) {
            $this->file_tagger->updateFilePath($curr_relative_filepath, $new_relative_file_path);
            $this->controllers->getLockedResourceController()->updatePath($curr_relative_filepath, $new_relative_file_path);
        };

        $response = $this->files_handler->renameFileViaRequest($request, $update_file_path);
        return $response;
    }

    /**
     * @Route("/files/action/move-multiple-files", name="move_multiple_multiple", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function moveMultipleFilesViaPost(Request $request){

        if( !$request->request->has(FilesHandler::KEY_FILES_CURRENT_PATHS) ){
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FilesHandler::KEY_FILES_CURRENT_PATHS;
            $this->app->logger->warning($message);;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        if (!$request->request->has(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR)) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR;
            $this->app->logger->warning($message);;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        if (!$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR)) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR;
            $this->app->logger->warning($message);;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        /** @info: this will be used to build single file transfer for each path */
        $files_current_paths = $request->request->get(FilesHandler::KEY_FILES_CURRENT_PATHS);

        $response_errors_data  = [];
        $response_success_data = [];

        foreach($files_current_paths as $file_current_path){
            $target_module_upload_dir                      = $request->request->get(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR);
            $subdirectory_target_path_in_module_upload_dir = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);

            $request = new Request();
            $request->request->set(FilesHandler::KEY_FILE_CURRENT_PATH, $file_current_path);
            $request->request->set(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR, $target_module_upload_dir);
            $request->request->set(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR, $subdirectory_target_path_in_module_upload_dir);

            try{
                $response = $this->moveSingleFileViaPost($request);
            }catch(Exception $e){
                $message  = $this->app->translator->translate("responses.files.thereWasAnErrorWhileTryingToMoveFile");
                $response = new Response($message);

                $this->app->logger->warning($message);;
                return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
            }
            $response_data = json_decode($response->getContent(), true);

            if( array_key_exists(self::KEY_RESPONSE_CODE, $response_data) ){

                if( $response->getStatusCode() >= 300){
                    $response_errors_data[] = [
                        self:: KEY_RESPONSE_DATA            => $response_data,
                        FilesHandler::KEY_FILE_CURRENT_PATH => $file_current_path,
                    ];
                }else{
                    $response_success_data = $response_data;
                }

            }

        }

        //all files copied
        if( empty($response_errors_data) ){
            $message = $this->app->translator->translate('responses.files.filesHasBeenSuccesfullyMoved');
            $code    = Response::HTTP_OK;
        }else{

            // all failed
            $message = $this->app->translator->translate('responses.files.couldNotTheFiles');
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;

            // some failed
            if( !empty($response_success_data) && !empty($response_errors_data) ) {
                $message = $this->app->translator->translate('responses.files.couldNotMoveSomeFiles');
                $code    = Response::HTTP_ACCEPTED;
            }

            $this->app->logger->warning($message, [
                self::KEY_RESPONSE_ERRORS_DATA => $response_errors_data
            ]);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
    }


    /**
     * @Route("/files/action/move-single-file", name="move_single_file", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function moveSingleFileViaPost(Request $request) {

        if (!$request->request->has(FilesHandler::KEY_FILE_CURRENT_PATH)) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FilesHandler::KEY_FILE_CURRENT_PATH;
            throw new \Exception($message);
        }

        if (!$request->request->has(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR)) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR;
            throw new \Exception($message);
        }

        if (!$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR)) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR;
            throw new \Exception($message);
        }

        $subdirectory_target_path_in_module_upload_dir  = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);
        $current_file_location                          = $request->request->get(FilesHandler::KEY_FILE_CURRENT_PATH);
        $target_module_upload_dir                       = $request->request->get(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR);

        $target_directory_in_upload_dir   = FileUploadController::getTargetDirectoryForUploadModuleDir($target_module_upload_dir);

        #checks if selected folder is the main upload dir (Main Folder)
        if ( $subdirectory_target_path_in_module_upload_dir === $target_directory_in_upload_dir ){
            $subdirectory_path_in_upload_dir = $target_directory_in_upload_dir;
        }else{
            $subdirectory_path_in_upload_dir  = $target_directory_in_upload_dir.DIRECTORY_SEPARATOR.$subdirectory_target_path_in_module_upload_dir;
        }

        $filename               = basename($current_file_location);
        $target_file_location   = FilesHandler::buildFileFullPathFromDirLocationAndFileName($subdirectory_path_in_upload_dir, $filename);

        $response = $this->files_handler->moveSingleFile($current_file_location, $target_file_location);

        $response_data = [
            self::KEY_RESPONSE_MESSAGE => $response->getContent(),
            self::KEY_RESPONSE_CODE    => $response->getStatusCode(),
        ];

        return new JsonResponse($response_data);
    }

    /**
     * @Route("/files/{upload_module_dir}/remove-subdirectory", name="upload_remove_subdirectory", methods="POST")
     * @param string $upload_module_dir
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function removeFolderByPostRequest(string $upload_module_dir, Request $request) {

        $block_removal = false;

        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR) ) {
            $message  = $this->app->translator->translate('exceptions.files.subdirectoryLocationMissingInRequest');
            $response = new Response($message, 500);
        }else{

            $current_directory_path_in_module_upload_dir = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);

            if( in_array($current_directory_path_in_module_upload_dir, FileUploadController::MODULES_UPLOAD_DIRS) ) {
                $message = $this->app->translator->translate('exceptions.files.cannotRemoveMainFolder');
                $response = new Response($message, 500);
            }
            else {

                if ( $request->request->has(DirectoriesHandler::KEY_BLOCK_REMOVAL) ) {
                    $block_removal = true;
                }

                $response = $this->directories_handler->removeFolder($upload_module_dir, $current_directory_path_in_module_upload_dir, $block_removal);

            }

        }

        $response_data = [
            'message' => $response->getContent(),
            'code'    => $response->getStatusCode()
        ];

        return new JsonResponse($response_data);
    }

    /**
     * It's possible to either call this method by using keys directly in data passed in ajax or serialized form
     * @Route("/files/actions/create-folder", name="action_create_subdirectory", methods="POST")
     * @param Request $request
     * @return Response
     * 
     * @throws \Exception
     */
    public function createSubdirectoryByPostRequest(Request $request){

        $isForm = $request->request->has(UploadSubdirectoryCreateType::FORM_NAME);

        switch ($isForm) {

            case true:

                $form = $request->request->get(UploadSubdirectoryCreateType::FORM_NAME);

                if ( !array_key_exists(FileUploadController::KEY_SUBDIRECTORY_NAME, $form) ) {
                    $message = $this->app->translator->translate('responses.general.missingFormInput') . FileUploadController::KEY_SUBDIRECTORY_NAME;
                    return new Response($message, 500);
                }

                if ( !array_key_exists(FileUploadController::KEY_UPLOAD_MODULE_DIR, $form) ) {
                    $message = $this->app->translator->translate('responses.general.missingFormInput') . FileUploadController::KEY_UPLOAD_MODULE_DIR;
                    return new Response($message, 500);
                }

                if ( !array_key_exists(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR, $form) ) {
                    $message = $this->app->translator->translate('responses.general.missingFormInput') . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR;
                    return new Response($message, 500);
                }

                $subdirectory_name  = $form[FileUploadController::KEY_SUBDIRECTORY_NAME];
                $upload_module_dir  = $form[FileUploadController::KEY_UPLOAD_MODULE_DIR];
                $target_directory_path_in_module_upload_dir = $form[FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR];

                break;
            case false:

                if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_NAME) ) {
                    $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_NAME;
                    return new Response($message, 500);
                }

                if ( !$request->request->has(FileUploadController::KEY_UPLOAD_MODULE_DIR) ) {
                    $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_NAME;
                    return new Response($message, 500);
                }

                if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR) ) {
                    $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_NAME;
                    return new Response($message, 500);
                }

                $subdirectory_name  = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_NAME);
                $upload_module_dir  = $request->request->get(FileUploadController::KEY_UPLOAD_MODULE_DIR);
                $target_directory_path_in_module_upload_dir = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);

                break;
        }

        $response = $this->directories_handler->createFolder($upload_module_dir, $subdirectory_name, $target_directory_path_in_module_upload_dir);

        $response_data = [
            'message' => $response->getContent(),
            'code'    => $response->getStatusCode()
        ];

        return new JsonResponse($response_data);
    }

    /**
     * Handles renaming of the folder via ajax call
     *
     * @Route("/files/actions/rename-folder", name="action_rename_subdirectory", methods="POST")
     * @param Request $request
     * @return Response
     *
     * @throws \Exception
     */
    public function renameFolderByPostRequest(Request $request)
    {
        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        if ( !$request->request->has(FileUploadController::KEY_UPLOAD_MODULE_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_UPLOAD_MODULE_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_NEW_NAME;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        $new_name             = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME);
        $upload_module_dir    = $request->request->get(FileUploadController::KEY_UPLOAD_MODULE_DIR);
        $current_directory_path_in_module_upload_dir = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);

        $response = $this->controllers->getFilesUploadSettingsController()->renameSubdirectory($upload_module_dir, $current_directory_path_in_module_upload_dir, $new_name);
        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/files/actions/move-or-copy-data-between-folders", name="actions_move_or_copy_data_between_folders", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function moveOrCopyDataBetweenFoldersViaPostRequest(Request $request): JsonResponse
    {
        if ( !$request->request->has(FilesHandler::KEY_CURRENT_UPLOAD_MODULE_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FilesHandler::KEY_CURRENT_UPLOAD_MODULE_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        if ( !$request->request->has(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        $current_upload_module_dir          = $request->request->get(FilesHandler::KEY_CURRENT_UPLOAD_MODULE_DIR);
        $target_upload_module_dir           = $request->request->get(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR);

        $current_directory_path_in_module_upload_dir  = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);
        $target_directory_path_in_module_upload_dir   = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);

        $do_move_entire_folder = Utils::getBoolRepresentationOfBoolString($request->request->get(UploadSubdirectoryCopyDataType::KEY_MOVE_FOLDER, false));

        try{
            if( $do_move_entire_folder ){

                $upload_dirs         = Env::getUploadDirs();
                $current_folder_path = $current_directory_path_in_module_upload_dir;
                $target_folder_path  = $target_directory_path_in_module_upload_dir;

                //if not main folder then add upload dir
                if( !in_array($current_directory_path_in_module_upload_dir, $upload_dirs) ){
                    $current_folder_path =  Env::getUploadDir() . DIRECTORY_SEPARATOR . $current_upload_module_dir . DIRECTORY_SEPARATOR . $current_directory_path_in_module_upload_dir;
                }

                //if not main folder then add upload dir
                if( !in_array($target_directory_path_in_module_upload_dir, $upload_dirs) ){
                    $target_folder_path  =  Env::getUploadDir() . DIRECTORY_SEPARATOR . $target_upload_module_dir . DIRECTORY_SEPARATOR . $target_directory_path_in_module_upload_dir;
                }

                $response = $this->directories_handler->moveDirectory($current_folder_path, $target_folder_path);
            }else{
                $response = $this->files_handler->copyData(
                    $current_upload_module_dir, $target_upload_module_dir, $current_directory_path_in_module_upload_dir, $target_directory_path_in_module_upload_dir
                );
            }

        }catch(\Exception | TypeError $e ){

            $this->app->logger->critical("Exception was thrown while calling folders data transfer logic", [
                "exceptionMessage" => $e->getMessage(),
                "exceptionCode"    => $e->getCode(),
                "exceptionTrace"   => $e->getTraceAsString(),
            ]);

            $message = $this->app->translator->translate('messages.general.internalServerError');
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

}