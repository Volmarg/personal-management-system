<?php


namespace App\Controller\Files;

use App\Controller\Utils\Application;
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

    public function __construct(FilesHandler $filesHandler, Application $app) {
        $this->app          = $app;
        $this->filesHandler = $filesHandler;
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

        if (!$request->request->has(FilesHandler::KEY_TARGET_SUBDIRECTORY_NAME)) {
            throw new \Exception('Missing request parameter named: ' . FilesHandler::KEY_TARGET_SUBDIRECTORY_NAME);
        }

        $target_subdirectory_name   = $request->request->get(FilesHandler::KEY_TARGET_SUBDIRECTORY_NAME);
        $current_file_location      = $request->request->get(FilesHandler::KEY_FILE_CURRENT_PATH);
        $target_upload_type         = $request->request->get(FilesHandler::KEY_TARGET_UPLOAD_TYPE);

        $target_directory           = FileUploadController::getTargetDirectoryForUploadType($target_upload_type);
        $subdirectory_path          = FileUploadController::getSubdirectoryPath($target_directory, $target_subdirectory_name);

        $filename                   = basename($current_file_location);
        $target_file_location       = $subdirectory_path.'/'.$filename;

        $response = $this->filesHandler->moveSingleFile($current_file_location, $target_file_location);

        $response_data = [
            'response_message' => $response->getContent(),
            'response_code'    => $response->getStatusCode(),
        ];

        return new JsonResponse($response_data);
    }


}