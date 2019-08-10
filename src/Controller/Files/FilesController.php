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

    /*
     * TODO
     *  add file renaming
     *  add file removing
     *  add moving files between folders
     *      even the ones from files type to images types
     *  files download.... do I even need this.. can js handle it?
     */


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
     */
    public function removeFileViaPost(Request $request) {
        $response = $this->filesHandler->removeFile($request);
        return $response;
    }

}