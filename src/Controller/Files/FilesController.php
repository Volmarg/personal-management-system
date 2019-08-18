<?php


namespace App\Controller\Files;

use App\Controller\Utils\Application;
use App\Services\FilesHandler;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

}