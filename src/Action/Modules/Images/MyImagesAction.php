<?php

namespace App\Action\Modules\Images;

use App\Controller\Files\FilesController;
use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FileUploadController;
use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Modules\Images\MyImagesController;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Action\Core\DialogsAction;
use App\Controller\Core\Env;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\ModuleData;
use App\Entity\System\LockedResource;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use App\Services\Files\ImageHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\System\ModuleAnnotation;

/**
 * Class MyImagesAction
 * @package App\Action\Modules\Images
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_IMAGES
 * )
 */
class MyImagesAction extends AbstractController {

    const TWIG_TEMPLATE_MY_IMAGES         = 'modules/my-images/my-images.html.twig';
    const TWIG_TEMPLATE_MY_FILES_SETTINGS = 'modules/my-images/settings.html.twig';

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

    public function __construct(FilesTagsController $files_tags_controller, Application $app, Controllers $controllers) {

        $this->controllers         = $controllers;
        $this->filesTagsController = $files_tags_controller;

        $this->app = $app;
    }

    /**
     * @Route("my-images/dir/{encodedSubdirectoryPath?}", name="modules_my_images")
     * @param string|null $encodedSubdirectoryPath
     * @param Request $request
     * @return Response
     *
     * @throws Exception
     */
    public function displayImages(? string $encodedSubdirectoryPath, Request $request) {

        $decodedSubdirectoryPath = FilesHandler::trimFirstAndLastSlash(urldecode($encodedSubdirectoryPath));
        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($decodedSubdirectoryPath);
        }

        $templateContent = $this->renderCategoryTemplate($decodedSubdirectoryPath, true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getImagesCategoryPageTitle($decodedSubdirectoryPath));

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("my-images/settings", name="modules_my_images_settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displaySettings(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsTemplate();
        }

        $templateContent  = $this->renderSettingsTemplate(true)->getContent();
        $ajaxResponse = new AjaxResponse("", $templateContent);
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
        return $this->render(static::TWIG_TEMPLATE_MY_FILES_SETTINGS, $data);
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

        $moduleUploadDir                   = Env::getImagesUploadDir();
        $subdirectoryPathInModuleUploadDir = FileUploadController::getSubdirectoryPath($moduleUploadDir, $decodedSubdirectoryPath);

        $moduleUploadDirName = FilesHandler::getModuleUploadDirForUploadPath($moduleUploadDir);
        $moduleName          = FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME[$moduleUploadDirName];

        if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($subdirectoryPathInModuleUploadDir, LockedResource::TYPE_DIRECTORY, $moduleName)         ){
            return $this->redirect('/');
        }

        if( !file_exists($subdirectoryPathInModuleUploadDir) ){
            $subdirectoryName = basename($decodedSubdirectoryPath);
            $this->addFlash('danger', "Folder '{$subdirectoryName} does not exist.");
            return $this->redirectToRoute('upload');
        }

        if (empty($decodedSubdirectoryPath)) {
            $allImages                  = $this->controllers->getMyImagesController()->getMainFolderImages();
        } else {
            $decodedSubdirectoryPath   = urldecode($decodedSubdirectoryPath);
            $allImages                  = $this->controllers->getMyImagesController()->getImagesFromCategory($decodedSubdirectoryPath);
        }

        foreach($allImages as $index => $image){
            $correspondingMiniaturePath = Env::getMiniaturesUploadDir() . DIRECTORY_SEPARATOR . FilesController::stripUploadDirectoryFromFilePathFront($image[MyFilesController::KEY_FILE_FULL_PATH]);
            if( file_exists($correspondingMiniaturePath) ){
                $allImages[$index][ImageHandler::KEY_MINIATURE_PATH] = $correspondingMiniaturePath;
                continue;
            }
        }

        # count files in dir tree - disables button for folder removing on front
        $searchDir           = (empty($decodedSubdirectoryPath) ? $moduleUploadDir : $subdirectoryPathInModuleUploadDir);
        $filesCountInTree    = FilesHandler::countFilesInTree($searchDir);

        $isMainDir  = ( empty($decodedSubdirectoryPath) );
        $uploadPath = Env::getImagesUploadDir() . DIRECTORY_SEPARATOR . $decodedSubdirectoryPath;

        $moduleData = $this->controllers->getModuleDataController()->getOneByRecordTypeModuleAndRecordIdentifier(
            ModuleData::RECORD_TYPE_DIRECTORY,
            ModulesController::MODULE_NAME_IMAGES,
            $uploadPath
        );

        $data = [
            'ajax_render'                    => $ajaxRender,
            'all_images'                     => $allImages,
            'subdirectory_path'              => $decodedSubdirectoryPath,
            'files_count_in_tree'            => $filesCountInTree,
            'module_data'                    => $moduleData,
            'upload_path'                    => $uploadPath,
            'upload_module_dir'              => MyImagesController::TARGET_UPLOAD_DIR,
            'is_main_dir'                    => $isMainDir,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getImagesCategoryPageTitle($decodedSubdirectoryPath),
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
    public function update(Request $request): Response
    {

        if (!$request->request->has(DialogsAction::KEY_FILE_CURRENT_PATH)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . DialogsAction::KEY_FILE_CURRENT_PATH;
            throw new Exception($message);
        }

        if (!$request->request->has(FileTagger::KEY_TAGS)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileTagger::KEY_TAGS;
            throw new Exception($message);
        }

        $fileCurrentPath = $request->request->get(DialogsAction::KEY_FILE_CURRENT_PATH);
        $tagsString      = $request->request->get(FileTagger::KEY_TAGS);


        try{
            $this->filesTagsController->updateTags($tagsString, $fileCurrentPath);
            $message = $this->app->translator->translate('responses.tagger.tagsUpdated');
            $code    = Response::HTTP_OK;
        } catch (Exception $e){
            $message = $this->app->translator->translate('exceptions.tagger.thereWasAnError');
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
    }

    /**
     * Will return the page title for images category page
     *
     * @param string $subdirectoryPath
     * @return string
     */
    private function getImagesCategoryPageTitle(string $subdirectoryPath): string
    {
        $pageTitle = $this->app->translator->translate(
            'images.title',
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
        return $this->app->translator->translate('images.settings.title');
    }

}