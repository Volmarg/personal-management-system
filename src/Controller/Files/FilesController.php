<?php


namespace App\Controller\Files;

use App\Controller\Utils\Application;
use App\Form\Files\UploadSubdirectoryCreateType;
use App\Services\DirectoriesHandler;
use App\Services\FilesHandler;
use App\Services\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;

class FilesController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var FilesHandler
     */
    private $filesHandler;

    /**
     * @var DirectoriesHandler $directoriesHandler
     */
    private $directoriesHandler;

    /**
     * @var FileTagger $file_tagger
     */
    private $file_tagger;

    public function __construct(FilesHandler $filesHandler, DirectoriesHandler $directoriesHandler, Application $app, FileTagger $file_tagger) {
        $this->app                  = $app;
        $this->file_tagger          = $file_tagger;
        $this->filesHandler         = $filesHandler;
        $this->directoriesHandler   = $directoriesHandler;
    }


    /**
     * @Route("/files/action/remove-file", name="files_remove_file", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function removeFileViaPost(Request $request) {
        $response = $this->filesHandler->removeFile($request);
        return $response;
    }

    /**
     * @Route("/files/action/rename-file", name="files_rename_file", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function renameFileViaPost(Request $request) {

        $update_file_path_for_tags = function ($curr_relative_filepath, $new_relative_file_path) {
            $this->file_tagger->updateFilePath($curr_relative_filepath, $new_relative_file_path);
        };

        $response = $this->filesHandler->renameFileViaRequest($request, $update_file_path_for_tags);
        return $response;
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

        $response = $this->filesHandler->moveSingleFile($current_file_location, $target_file_location);

        $response_data = [
            'response_message' => $response->getContent(),
            'response_code'    => $response->getStatusCode(),
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
            $message = $this->app->translator->translate('exceptions.files.subdirectoryLocationMissingInRequest');
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

                $response = $this->directoriesHandler->removeFolder($upload_module_dir, $current_directory_path_in_module_upload_dir, $block_removal);

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

        $response = $this->directoriesHandler->createFolder($upload_module_dir, $subdirectory_name, $target_directory_path_in_module_upload_dir);

        $response_data = [
            'message' => $response->getContent()
        ];

        return new JsonResponse($response_data);
    }

}