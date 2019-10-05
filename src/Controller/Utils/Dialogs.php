<?php

namespace App\Controller\Utils;

use App\Controller\Files\FileUploadController;
use App\Services\DirectoriesHandler;
use App\Services\FilesHandler;
use App\Services\FileTagger;
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
    const TWIG_TEMPLATE_DIALOG_BODY_UPDATE_TAGS    = 'page-elements/components/dialogs/bodies/update-tags.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_NEW_FOLDER     = 'page-elements/components/dialogs/bodies/new-folder.twig';
    const KEY_FILE_CURRENT_PATH                    = 'fileCurrentPath';
    const KEY_MODULE_NAME                          = 'moduleName';

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var FileTagger $file_tagger
     */
    private $file_tagger;

    /**
     * @var DirectoriesHandler $directories_handler
     */
    private $directories_handler;

    public function __construct(Application $app, FileTagger $file_tagger, DirectoriesHandler $directories_handler) {
        $this->app                      = $app;
        $this->file_tagger              = $file_tagger;
        $this->directories_handler      = $directories_handler;
    }

    /**
     * @Route("/dialog/body/data-transfer", name="dialog_body_data_transfer", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function buildDataTransferDialogBody(Request $request) {

        if( !$request->request->has(static::KEY_FILE_CURRENT_PATH) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_FILE_CURRENT_PATH;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        if( !$request->request->has(static::KEY_MODULE_NAME) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_MODULE_NAME;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $module_name  = $request->request->get(static::KEY_MODULE_NAME);

        if( !array_key_exists($module_name, FileUploadController::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES) ){
            $message = $this->app->translator->translate('responses.upload.moduleNameIsIncorrect');
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        // in ligthgallery.html.twig
        $file_current_path = FilesHandler::trimFirstAndLastSlash($request->request->get(static::KEY_FILE_CURRENT_PATH));

        $file = new File($file_current_path);

        if( !$file->isFile() ){
            $message = $this->app->translator->translate('responses.files.filePathIsIncorrectFileDoesNotExist');
            return new JsonResponse([
                'errorMessage' => $message
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


    /**
     * @Route("/dialog/body/tags-update", name="dialog_body_tags_update", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function buildTagsUpdateDialogBody(Request $request) {

        if( !$request->request->has(static::KEY_FILE_CURRENT_PATH) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_FILE_CURRENT_PATH;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $module_name  = $request->request->get(static::KEY_MODULE_NAME);

        if( !array_key_exists($module_name, FileUploadController::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES) ){
            $message = $this->app->translator->translate('responses.upload.moduleNameIsIncorrect');
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        // in ligthgallery.html.twig
        $file_current_path = FilesHandler::trimFirstAndLastSlash($request->request->get(static::KEY_FILE_CURRENT_PATH));

        $file = new File($file_current_path);

        if( !$file->isFile() ){
            $message = $this->app->translator->translate('responses.files.filePathIsIncorrectFileDoesNotExist');
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $this->file_tagger->prepare([],$file_current_path);
        $file_tags = $this->app->repositories->filesTagsRepository->getFileTagsEntityByFileFullPath($file_current_path);
        $tags_json = ( !is_null($file_tags) ? $file_tags->getTags() : '');

        $form_data  = [
            FileTagger::KEY_TAGS=> $tags_json
        ];
        $form = $this->app->forms->updateTags($form_data);

        $template_data = [
            'form' => $form->createView()
        ];

        $rendered_view = $this->render(static::TWIG_TEMPLATE_DIALOG_BODY_UPDATE_TAGS, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }

    /**
     * @Route("/dialog/body/create-folder", name="dialog_body_create_folder", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function buildCreateNewFolderDialogBody(Request $request) {

        $create_subdir_form = $this->app->forms->uploadCreateSubdirectory();

        $template_data = [
          'form' => $create_subdir_form->createView()
        ];

        $rendered_view = $this->render(static::TWIG_TEMPLATE_DIALOG_BODY_NEW_FOLDER, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }

}
