<?php


namespace App\Controller\Files;

use App\Controller\Utils\Application;
use App\Services\DirectoriesHandler;
use App\Services\FilesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    public function __construct(FilesHandler $filesHandler, DirectoriesHandler $directoriesHandler, Application $app) {
        $this->app                  = $app;
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
        $response = $this->filesHandler->renameFile($request);
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
            throw new \Exception('Missing request parameter named: ' . FilesHandler::KEY_FILE_CURRENT_PATH);
        }

        if (!$request->request->has(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR)) {
            throw new \Exception('Missing request parameter named: ' . FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR);
        }

        if (!$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR)) {
            throw new \Exception('Missing request parameter named: ' . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);
        }

        $subdirectory_target_path_in_module_upload_dir  = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);
        $current_file_location                          = $request->request->get(FilesHandler::KEY_FILE_CURRENT_PATH);
        $target_module_upload_dir                       = $request->request->get(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR);

        $target_directory_in_upload_dir   = FileUploadController::getTargetDirectoryForUploadModuleDir($target_module_upload_dir);

        #checks if selected folder is the main upload dir (Main Folder)
        if ( $subdirectory_target_path_in_module_upload_dir === $target_directory_in_upload_dir ){
            $subdirectory_path_in_upload_dir = $target_directory_in_upload_dir;
        }else{
            $subdirectory_path_in_upload_dir  = $target_directory_in_upload_dir.'/'.$subdirectory_target_path_in_module_upload_dir;
        }

        $filename               = basename($current_file_location);
        $target_file_location   = $subdirectory_path_in_upload_dir.'/'.$filename;

        //In some cases the path starts with "/" on frontend and this is required there but here we want path without it
        if( preg_match("#^\/#", $current_file_location) ){
            $current_file_location = preg_replace('#^\/#','',$current_file_location);
        }

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
            $response = new Response("Subdirectory location is missing in request.", 500);
        }else{

            $current_directory_path_in_module_upload_dir = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);

            if( in_array($current_directory_path_in_module_upload_dir, FileUploadController::MODULES_UPLOAD_DIRS) ) {
                $response = new Response("Cannot remove main folder!", 500);
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



}