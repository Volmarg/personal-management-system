<?php

namespace App\Controller\Utils;

use App\Controller\Files\FileUploadController;
use App\Services\FilesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This class is only responsible for building dialogs data in response for example on Ajax call
 * Class Dialogs
 * @package App\Controller\Utils
 */
class Dialogs extends AbstractController
{
    const TWIG_TEMPLATE_DIALOG_BODY_FILES_TRANSFER = 'page-elements/components/dialogs/bodies/files-transfer.html.twig';
    const KEY_FILE_CURRENT_PATH                    = 'fileCurrentPath';
    const KEY_MODULE_NAME                          = 'moduleName';

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/dialog/body/data-transfer", name="dialog_body_data_transfer", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function buildDataTransferDialogBody(Request $request) {

        if( !$request->request->has(static::KEY_FILE_CURRENT_PATH) ){
            return new JsonResponse([
                'errorMessage' => "Request is missing key: ".static::KEY_FILE_CURRENT_PATH
            ]);
        }

        if( !$request->request->has(static::KEY_MODULE_NAME) ){
            return new JsonResponse([
                'errorMessage' => "Request is missing key: ".static::KEY_MODULE_NAME
            ]);
        }

        $module_name  = $request->request->get(static::KEY_MODULE_NAME);

        if( !array_key_exists($module_name, FileUploadController::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES) ){
            return new JsonResponse([
                'errorMessage' => "Module name is incorrect."
            ]);
        }

        $file_current_path = $request->request->get(static::KEY_FILE_CURRENT_PATH);

        //In some cases the path starts with "/" on frontend and this is required there but here we want path without it
        if( preg_match("#^\/#", $file_current_path) ){
            $file_current_path = preg_replace('#^\/#','', $file_current_path);
        }

        $file = new File($file_current_path);

        if( !$file->isFile() ){
            return new JsonResponse([
                'errorMessage' => "File provided in filepath is incorrect - such file does not exist"
            ]);
        }

        $all_upload_based_modules = FileUploadController::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES;

        $form_data  = [
            FilesHandler::KEY_MODULES_NAMES => $all_upload_based_modules
        ];
        $form = $this->app->forms->moveSingleFile($form_data);

        $template_data = [
            'form' => $form->createView()
        ];

        $rendered_view = $this->render(static::TWIG_TEMPLATE_DIALOG_BODY_FILES_TRANSFER, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }

}
