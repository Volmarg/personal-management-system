<?php

namespace App\Controller\Utils;

use App\Controller\Files\FileUploadController;
use App\Services\FilesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This class is only responsible for building dialogs data in response for example on Ajax call
 * Class Dialogs
 * @package App\Controller\Utils
 */
class Dialogs extends AbstractController
{
    const TWIG_TEMPLATE_DIALOG_BODY_FILES_TRANSFER = 'page-elements/components/dialogs/bodies/files-transfer.html.twig';

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/dialog/body/data-transfer", name="dialog_body_data_transfer")
     * @throws \Exception
     */
    public function buildDataTransferDialogBody() {
        #Todo: reuse logic from moving entire data between folders, I can split it and make optional params (?)
        $all_subdirectories_for_all_types = FileUploadController::getSubdirectoriesForAllUploadTypes(true);
        $form_data                        = [FilesHandler::KEY_TARGET_SUBDIRECTORY_NAME => $all_subdirectories_for_all_types];

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
