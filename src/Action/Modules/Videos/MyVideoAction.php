<?php

namespace App\Action\Modules\Videos;

use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FileUploadController;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Env;
use App\Controller\Modules\Video\MyVideoController;
use App\Entity\System\LockedResource;
use App\Services\Files\FilesHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyVideoAction extends AbstractController {

    const TWIG_TEMPLATE_MY_VIDEO          = 'modules/my-video/my-video.html.twig';
    const TWIG_TEMPLATE_MY_VIDEO_SETTINGS = 'modules/my-video/settings.html.twig';

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
     * @Route("my-video/dir/{encoded_subdirectory_path?}", name="modules_my_video")
     * @param string|null $encoded_subdirectory_path
     * @param Request $request
     * @return Response
     *
     * @throws Exception|\Doctrine\DBAL\Driver\Exception
     */
    public function displayVideos(? string $encoded_subdirectory_path, Request $request) {
        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($encoded_subdirectory_path, false);
        }

        $template_content  = $this->renderCategoryTemplate($encoded_subdirectory_path, true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("my-video/settings", name="modules_my_video_settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displaySettings(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsTemplate(false);
        }

        $template_content  = $this->renderSettingsTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
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
        return $this->render(static::TWIG_TEMPLATE_MY_VIDEO_SETTINGS, $data);
    }

    /**
     * @param string|null $encoded_subdirectory_path
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return array|RedirectResponse|Response
     *
     * @throws Exception|\Doctrine\DBAL\Driver\Exception
     */
    private function renderCategoryTemplate(? string $encoded_subdirectory_path, bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $module_upload_dir                      = Env::getVideoUploadDir();
        $decoded_subdirectory_path              = FilesHandler::trimFirstAndLastSlash(urldecode($encoded_subdirectory_path));
        $subdirectory_path_in_module_upload_dir = FileUploadController::getSubdirectoryPath($module_upload_dir, $decoded_subdirectory_path);

        $module_upload_dir_name = FilesHandler::getModuleUploadDirForUploadPath($module_upload_dir);
        $module_name            = FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME[$module_upload_dir_name];

        if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($subdirectory_path_in_module_upload_dir, LockedResource::TYPE_DIRECTORY, $module_name) ){
            return $this->redirect('/');
        }

        if( !file_exists($subdirectory_path_in_module_upload_dir) ){
            $subdirectory_name = basename($decoded_subdirectory_path);
            $this->addFlash('danger', "Folder '{$subdirectory_name} does not exist.");
            return $this->redirectToRoute('upload');
        }

        if (empty($decoded_subdirectory_path)) {
            $all_video                  = $this->controllers->getMyVideoController()->getMainFolderVideos();
        } else {
            $decoded_subdirectory_path   = urldecode($decoded_subdirectory_path);
            $all_video                  = $this->controllers->getMyVideoController()->getVideosInCategory($decoded_subdirectory_path);
        }

        # count files in dir tree - disables button for folder removing on front
        $searchDir              = (empty($decoded_subdirectory_path) ? $module_upload_dir : $subdirectory_path_in_module_upload_dir);
        $files_count_in_tree    = FilesHandler::countFilesInTree($searchDir);

        $is_main_dir = ( empty($decoded_subdirectory_path) );

        $data = [
            'ajax_render'                    => $ajax_render,
            'all_video'                      => $all_video,
            'subdirectory_path'              => $decoded_subdirectory_path,
            'files_count_in_tree'            => $files_count_in_tree,
            'upload_module_dir'              => MyVideoController::getTargetUploadDir(),
            'is_main_dir'                    => $is_main_dir,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_VIDEO, $data);
    }

}