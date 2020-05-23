<?php

namespace App\Action\Modules\Images;

use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FileUploadController;
use App\Controller\Modules\Images\MyImagesController;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Action\Core\DialogsAction;
use App\Controller\Core\Env;
use App\Entity\System\LockedResource;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyImagesAction extends AbstractController {

    const TWIG_TEMPLATE_MY_IMAGES         = 'modules/my-images/my-images.html.twig';
    const TWIG_TEMPLATE_MY_FILES_SETTINGS = 'modules/my-images/settings.html.twig';

    /**
     * @var FilesTagsController $files_tags_controller
     */
    private $files_tags_controller;

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(FilesTagsController $files_tags_controller, Application $app, Controllers $controllers) {

        $this->controllers                = $controllers;
        $this->files_tags_controller      = $files_tags_controller;

        $this->app = $app;
    }

    /**
     * @Route("my-images/dir/{encoded_subdirectory_path?}", name="modules_my_images")
     * @param string|null $encoded_subdirectory_path
     * @param Request $request
     * @return Response
     * 
     */
    public function displayImages(? string $encoded_subdirectory_path, Request $request) {
        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($encoded_subdirectory_path, false);
        }

        $template_content  = $this->renderCategoryTemplate($encoded_subdirectory_path, true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("my-images/settings", name="modules_my_images_settings")
     * @param Request $request
     * @return Response
     */
    public function displaySettings(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsTemplate(false);
        }

        $template_content  = $this->renderSettingsTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    private function renderSettingsTemplate(bool $ajax_render = false): Response
    {
        $data = [
            'ajax_render' => $ajax_render,
        ];
        return $this->render(static::TWIG_TEMPLATE_MY_FILES_SETTINGS, $data);
    }

    /**
     * @param string|null $encoded_subdirectory_path
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return array|RedirectResponse|Response
     *
     * @throws Exception
     */
    private function renderCategoryTemplate(? string $encoded_subdirectory_path, bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $module_upload_dir                      = Env::getImagesUploadDir();
        $decoded_subdirectory_path              = FilesHandler::trimFirstAndLastSlash(urldecode($encoded_subdirectory_path));
        $subdirectory_path_in_module_upload_dir = FileUploadController::getSubdirectoryPath($module_upload_dir, $decoded_subdirectory_path);

        $module_upload_dir_name = FilesHandler::getModuleUploadDirForUploadPath($module_upload_dir);
        $module_name            = FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME[$module_upload_dir_name];

        if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($subdirectory_path_in_module_upload_dir, LockedResource::TYPE_DIRECTORY, $module_name)         ){
            return $this->redirect('/');
        }

        if( !file_exists($subdirectory_path_in_module_upload_dir) ){
            $subdirectory_name = basename($decoded_subdirectory_path);
            $this->addFlash('danger', "Folder '{$subdirectory_name} does not exist.");
            return $this->redirectToRoute('upload');
        }

        if (empty($decoded_subdirectory_path)) {
            $all_images                 = $this->controllers->getMyImagesController()->getMainFolderImages();
        } else {
            $decoded_subdirectory_path   = urldecode($decoded_subdirectory_path);
            $all_images                  = $this->controllers->getMyImagesController()->getImagesFromCategory($decoded_subdirectory_path);
        }

        # count files in dir tree - disables button for folder removing on front
        $searchDir              = (empty($decoded_subdirectory_path) ? $module_upload_dir : $subdirectory_path_in_module_upload_dir);
        $files_count_in_tree    = FilesHandler::countFilesInTree($searchDir);

        $is_main_dir = ( empty($decoded_subdirectory_path) );

        $data = [
            'ajax_render'                    => $ajax_render,
            'all_images'                     => $all_images,
            'subdirectory_path'              => $decoded_subdirectory_path,
            'files_count_in_tree'            => $files_count_in_tree,
            'upload_module_dir'              => MyImagesController::TARGET_UPLOAD_DIR,
            'is_main_dir'                    => $is_main_dir,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_IMAGES, $data);
    }


    /**
     * Handles tags updating for the plugin modal
     * @Route("/api/my-images/update-tags", name="api_my_images_update_tags", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function update(Request $request){

        if (!$request->request->has(DialogsAction::KEY_FILE_CURRENT_PATH)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . DialogsAction::KEY_FILE_CURRENT_PATH;
            throw new Exception($message);
        }

        if (!$request->request->has(FileTagger::KEY_TAGS)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileTagger::KEY_TAGS;
            throw new Exception($message);
        }

        $file_current_path = $request->request->get(DialogsAction::KEY_FILE_CURRENT_PATH);
        $tags_string       = $request->request->get(FileTagger::KEY_TAGS);


        try{
            $this->files_tags_controller->updateTags($tags_string, $file_current_path);
            $message = $this->app->translator->translate('responses.tagger.tagsUpdated');
        } catch (Exception $e){
            $message = $this->app->translator->translate('exceptions.tagger.thereWasAnError');
        }

        $response_data = [
            'response_code'     => 200,
            'response_message'  => $message
        ];

        return new JsonResponse($response_data);
    }

}