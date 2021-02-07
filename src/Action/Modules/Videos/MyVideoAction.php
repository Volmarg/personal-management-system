<?php

namespace App\Action\Modules\Videos;

use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FileUploadController;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Env;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Video\MyVideoController;
use App\Entity\Modules\ModuleData;
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
    private FilesTagsController $files_tags_controller;

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

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

        $templateContent = $this->renderCategoryTemplate($encoded_subdirectory_path, true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $templateContent);
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
            return $this->renderSettingsTemplate();
        }

        $templateContent = $this->renderSettingsTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $templateContent);
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
     * @param string|null $encodedSubdirectoryPath
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return array|RedirectResponse|Response
     *
     * @throws Exception|\Doctrine\DBAL\Driver\Exception
     */
    private function renderCategoryTemplate(? string $encodedSubdirectoryPath, bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false) {

        $moduleUploadDir                   = Env::getVideoUploadDir();
        $decodedSubdirectoryPath           = FilesHandler::trimFirstAndLastSlash(urldecode($encodedSubdirectoryPath));
        $subdirectoryPathInModuleUploadDir = FileUploadController::getSubdirectoryPath($moduleUploadDir, $decodedSubdirectoryPath);

        $moduleUploadDirName = FilesHandler::getModuleUploadDirForUploadPath($moduleUploadDir);
        $moduleName          = FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME[$moduleUploadDirName];

        if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($subdirectoryPathInModuleUploadDir, LockedResource::TYPE_DIRECTORY, $moduleName) ){
            return $this->redirect('/');
        }

        if( !file_exists($subdirectoryPathInModuleUploadDir) ){
            $subdirectoryName = basename($decodedSubdirectoryPath);
            $this->addFlash('danger', "Folder '{$subdirectoryName} does not exist.");
            return $this->redirectToRoute('upload');
        }

        if (empty($decodedSubdirectoryPath)) {
            $allVideo = $this->controllers->getMyVideoController()->getMainFolderVideos();
        } else {
            $decodedSubdirectoryPath = urldecode($decodedSubdirectoryPath);
            $allVideo = $this->controllers->getMyVideoController()->getVideosInCategory($decodedSubdirectoryPath);
        }

        # count files in dir tree - disables button for folder removing on front
        $searchDir              = (empty($decodedSubdirectoryPath) ? $moduleUploadDir : $subdirectoryPathInModuleUploadDir);
        $filesCountInTree    = FilesHandler::countFilesInTree($searchDir);

        $isMainDir  = ( empty($decodedSubdirectoryPath) );
        $uploadPath = Env::getVideoUploadDir() . DIRECTORY_SEPARATOR . $decodedSubdirectoryPath;

        $moduleData = $this->controllers->getModuleDataController()->getOneByRecordTypeModuleAndRecordIdentifier(
            ModuleData::RECORD_TYPE_DIRECTORY,
            ModulesController::MODULE_NAME_VIDEO,
            $uploadPath
        );

        $data = [
            'ajax_render'                    => $ajaxRender,
            'module_data'                    => $moduleData,
            'upload_path'                    => $uploadPath,
            'all_video'                      => $allVideo,
            'subdirectory_path'              => $decodedSubdirectoryPath,
            'files_count_in_tree'            => $filesCountInTree,
            'upload_module_dir'              => MyVideoController::getTargetUploadDir(),
            'is_main_dir'                    => $isMainDir,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_VIDEO, $data);
    }

}