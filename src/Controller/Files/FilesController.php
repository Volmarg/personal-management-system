<?php


namespace App\Controller\Files;

use App\Controller\Utils\Application;
use App\Services\DirectoriesHandler;
use App\Services\FilesHandler;
use App\Services\FileUploader;
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
     * @Route("/upload/action/remove-file", name="upload_remove_file", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function removeFileViaPost(Request $request) {
        $response = $this->filesHandler->removeFile($request);
        return $response;
    }

    /**
     * @Route("/upload/action/rename-file", name="upload_rename_file", methods="POST")
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

        if (!$request->request->has(FilesHandler::KEY_TARGET_UPLOAD_TYPE)) {
            throw new \Exception('Missing request parameter named: ' . FilesHandler::KEY_TARGET_UPLOAD_TYPE);
        }

        if (!$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_UPLOAD_DIR)) {
            throw new \Exception('Missing request parameter named: ' . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_UPLOAD_DIR);
        }

        $subdirectory_target_path_in_upload_dir     = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_UPLOAD_DIR);
        $current_file_location                      = $request->request->get(FilesHandler::KEY_FILE_CURRENT_PATH);
        $target_upload_type                         = $request->request->get(FilesHandler::KEY_TARGET_UPLOAD_TYPE);

        $target_directory           = FileUploadController::getTargetDirectoryForUploadType($target_upload_type);
        $subdirectory_path          = $target_directory.'/'.$subdirectory_target_path_in_upload_dir;

        $filename                   = basename($current_file_location);
        $target_file_location       = $subdirectory_path.'/'.$filename;

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
     * @Route("/upload/{upload_type}/remove-subdirectory", name="upload_remove_subdirectory", methods="POST")
     * @param string $upload_type
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function removeFolderByPostRequest(string $upload_type, Request $request) {

        $block_removal = false;

        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_UPLOAD_DIR) ) {
            $response = new Response("Subdirectory location is missing in request.", 500);
        }else{

            if ( $request->request->has(DirectoriesHandler::KEY_BLOCK_REMOVAL) ) {
                $block_removal = true;
            }

            $current_directory_path_in_upload_type_dir  = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_UPLOAD_DIR);
            $response                                   = $this->directoriesHandler->removeFolder($upload_type, $current_directory_path_in_upload_type_dir, $block_removal);

        }


        $response_data = [
            'message' => $response->getContent(),
            'code'    => $response->getStatusCode()
        ];

        return new JsonResponse($response_data);
    }



}