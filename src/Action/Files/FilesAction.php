<?php


namespace App\Action\Files;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilesAction extends AbstractController {

    /**e
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
    private $controllers;

    /**
     * @var FilesHandler $files_handler
     */
    private $files_handler;

    /**
     * @var \App\Services\Files\FileTagger $file_tagger
     */
    private $file_tagger;

    public function __construct(
        Application  $app,
        FilesHandler $files_handler,
        FileTagger   $file_tagger
    ) {
        $this->app           = $app;
        $this->files_handler = $files_handler;
        $this->file_tagger   = $file_tagger;
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

        return AjaxResponse::buildResponseForAjaxCall($code, $message);
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
            $this->app->repositories->lockedResourceRepository->updatePath($curr_relative_filepath, $new_relative_file_path);
        };

        $response = $this->files_handler->renameFileViaRequest($request, $update_file_path);
        return $response;
    }

}