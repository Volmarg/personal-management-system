<?php


namespace App\Action\Files;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Modules\Files\MyFilesController;
use App\Services\Files\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @ModuleAnnotation(
 *     relatedModules=App\Controller\Modules\ModulesController::UPLOAD_MENU_RELATED_MODULES
 * )
 */
class FilesTagsAction extends AbstractController {


    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;


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
            $this->app->logger->critical($message);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        if (!$request->request->has(MyFilesController::KEY_FILE_FULL_PATH)){
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . MyFilesController::KEY_FILE_FULL_PATH;
            $this->app->logger->critical($message);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        $tagsString   = $request->request->get(FileTagger::KEY_TAGS);
        $fileFullPath = $request->request->get(MyFilesController::KEY_FILE_FULL_PATH);

        if( empty($fileFullPath) ){
            $message = $this->app->translator->translate('responses.files.filePathIsAnEmptyString');
            $this->app->logger->critical($message);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        $response = $this->controllers->getFilesTagsController()->updateTags($tagsString, $fileFullPath);
        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }


}