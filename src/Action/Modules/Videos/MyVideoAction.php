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
use App\Annotation\System\ModuleAnnotation;

/**
 * Class MyVideoAction
 * @package App\Action\Modules\Videos
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_VIDEO
 * )
 */
class MyVideoAction extends AbstractController {

    const TWIG_TEMPLATE_MY_VIDEO          = 'modules/my-video/my-video.html.twig';
    const TWIG_TEMPLATE_MY_VIDEO_SETTINGS = 'modules/my-video/settings.html.twig';

    /**
     * @var FilesTagsController $filesTagsController
     */
    private FilesTagsController $filesTagsController;

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(FilesTagsController $filesTagsController, Application $app, Controllers $controllers) {

        $this->controllers         = $controllers;
        $this->filesTagsController = $filesTagsController;

        $this->app = $app;
    }

    /**
     * @Route("my-video/dir/{encodedSubdirectoryPath?}", name="modules_my_video")
     * @param string|null $encodedSubdirectoryPath
     * @param Request $request
     * @return Response
     *
     * @throws Exception|\Doctrine\DBAL\Driver\Exception
     */
    public function displayVideos(? string $encodedSubdirectoryPath, Request $request) {

        $decodedSubdirectoryPath = FilesHandler::trimFirstAndLastSlash(urldecode($encodedSubdirectoryPath));
        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($decodedSubdirectoryPath, false);
        }

        $templateContent = $this->renderCategoryTemplate($decodedSubdirectoryPath, true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getVideoCategoryPageTitle($decodedSubdirectoryPath));

        return $ajaxResponse->buildJsonResponse();
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
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getSettingsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     */
    private function renderSettingsTemplate(bool $ajaxRender = false): Response
    {
        $data = [
            'ajax_render' => $ajaxRender,
            'page_title'  => $this->getSettingsPageTitle(),
        ];
        return $this->render(static::TWIG_TEMPLATE_MY_VIDEO_SETTINGS, $data);
    }

    /**
     * @param string|null $decodedSubdirectoryPath
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return array|RedirectResponse|Response
     *
     * @throws Exception|\Doctrine\DBAL\Driver\Exception
     */
    private function renderCategoryTemplate(? string $decodedSubdirectoryPath, bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false) {

        $moduleUploadDir                   = Env::getVideoUploadDir();
        $subdirectoryPathInModuleUploadDir = FileUploadController::getSubdirectoryPath($moduleUploadDir, $decodedSubdirectoryPath);

        $moduleUploadDirName = FilesHandler::getModuleUploadDirForUploadPath($moduleUploadDir);
        $moduleName          = FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME[$moduleUploadDirName];

        if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($subdirectoryPathInModuleUploadDir, LockedResource::TYPE_DIRECTORY, $moduleName) ){
            return $this->redirect('/');
        }

        if( !file_exists($subdirectoryPathInModuleUploadDir) ){
            $subdirectoryName = basename($decodedSubdirectoryPath);
            $this->addFlash('danger', "Folder '{$subdirectoryName} does not exist.");
            return $this->redirectToRoute('upload_fine_upload');
        }

        if (empty($decodedSubdirectoryPath)) {
            $allVideo = $this->controllers->getMyVideoController()->getMainFolderVideos();
        } else {
            $decodedSubdirectoryPath = urldecode($decodedSubdirectoryPath);
            $allVideo = $this->controllers->getMyVideoController()->getVideosInCategory($decodedSubdirectoryPath);
        }

        # count files in dir tree - disables button for folder removing on front
        $searchDir        = (empty($decodedSubdirectoryPath) ? $moduleUploadDir : $subdirectoryPathInModuleUploadDir);
        $filesCountInTree = FilesHandler::countFilesInTree($searchDir);

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
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getVideoCategoryPageTitle($decodedSubdirectoryPath),
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_VIDEO, $data);
    }

    /**
     * Will return the page title for video category page
     *
     * @param string $subdirectoryPath
     * @return string
     */
    private function getVideoCategoryPageTitle(string $subdirectoryPath): string
    {
        $pageTitle = $this->app->translator->translate(
            'video.title',
            [
                '{{folder}}' => basename($subdirectoryPath),
            ]
        );

        return $pageTitle;
    }

    /**
     * Will return settings page title
     *
     * @return string
     */
    private function getSettingsPageTitle(): string
    {
        return $this->app->translator->translate('video.settings.title');
    }

}