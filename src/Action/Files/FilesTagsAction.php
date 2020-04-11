<?php


namespace App\Action\Files;


use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Modules\Files\MyFilesController;
use App\Services\Files\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilesTagsAction extends AbstractController {


    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;


    public function __construct(Controllers $controllers, Application $app) {
        $this->app = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("api/files-tagger/update-tags", name="api_files_tagger_update_tags", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function apiUpdateTags(Request $request): Response {

        if (!$request->request->has(FileTagger::KEY_TAGS)){
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FileTagger::KEY_TAGS;
            throw new Exception($message);
        }

        if (!$request->request->has(MyFilesController::KEY_FILE_FULL_PATH)){
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . MyFilesController::KEY_FILE_FULL_PATH;
            throw new Exception($message);
        }

        if( empty($file_full_path) ){
            $message = $this->app->translator->translate('responses.files.filePathIsAnEmptyString');
            return new Response($message);
        }

        $tags_string    = $request->request->get(FileTagger::KEY_TAGS);
        $file_full_path = $request->request->get(MyFilesController::KEY_FILE_FULL_PATH);

        $response = $this->controllers->getFilesTagsController()->updateTags($tags_string, $file_full_path);
        return $response;
    }


}