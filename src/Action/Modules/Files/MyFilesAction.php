<?php
namespace App\Action\Modules\Files;

use App\Controller\Files\FileUploadController;
use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Env;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\ModuleData;
use App\Entity\System\LockedResource;
use App\Services\Files\FileDownloader;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\System\ModuleAnnotation;

/**
 * Class MyFilesAction
 * @package App\Action\Modules\Files
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_FILES
 * )
 */
class MyFilesAction extends AbstractController {

    const TWIG_TEMPLATE_MY_FILES          = 'modules/my-files/my-files.html.twig';
    const TWIG_TEMPLATE_MY_FILES_SETTINGS = 'modules/my-files/settings.html.twig';

    /**
     * @var Finder $finder
     */
    private Finder $finder;

    /**
     * @var FileDownloader $fileDownloader
     */
    private FileDownloader $fileDownloader;

    /**
     * @var FilesHandler $filesHandler
     */
    private FilesHandler $filesHandler;

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var FileTagger $fileTagger
     */
    private FileTagger $fileTagger;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(
        FileDownloader $fileDownloader,
        FilesHandler   $filesHandler,
        Application    $app,
        FileTagger     $fileTagger,
        Controllers    $controllers
    ) {
        $this->finder = new Finder();
        $this->finder->depth('== 0');

        $this->fileDownloader = $fileDownloader;
        $this->filesHandler   = $filesHandler;
        $this->app            = $app;
        $this->fileTagger     = $fileTagger;
        $this->controllers    = $controllers;
    }

    /**
     * Handles file renaming and tags updating
     * @Route("/api/my-files/update", name="my_files_update", methods="POST")
     * @param Request $request
     * @return Response|JsonResponse
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function update(Request $request){

        if (!$request->request->has(MyFilesController::KEY_FILE_FULL_PATH)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . MyFilesController::KEY_FILE_FULL_PATH;
            throw new Exception($message);
        }

        $subdirectory = $request->request->get(MyFilesController::KEY_SUBDIRECTORY);
        $tagsString   = $request->request->get(FileTagger::KEY_TAGS);

        $updateFilePath = function ($currRelativeFilepath, $newRelativeFilePath) use($tagsString) {
            $this->fileTagger->updateFilePath($currRelativeFilepath, $newRelativeFilePath);
            $this->controllers->getFilesTagsController()->updateTags($tagsString, $newRelativeFilePath);
            $this->controllers->getLockedResourceController()->updatePath($currRelativeFilepath, $newRelativeFilePath);
        };

        $this->filesHandler->renameFileViaRequest($request, $updateFilePath);

        // It's ok, further logic decides if that's ajax call or not and sends either json response or response
        return $this->displayFiles($subdirectory, $request);
    }

    /**
     * @Route("my-files/dir/{encodedSubdirectoryPath?}", name="modules_my_files")
     * @param string|null $encodedSubdirectoryPath
     * @param Request $request
     * @return Response
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function displayFiles(? string $encodedSubdirectoryPath, Request $request): Response
    {
        $decodedSubdirectoryPath = urldecode($encodedSubdirectoryPath);
        $subdirectoryPath        = FilesHandler::trimFirstAndLastSlash($decodedSubdirectoryPath);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($subdirectoryPath);
        }

        $templateContent = $this->renderCategoryTemplate($subdirectoryPath, true)->getContent();
        $message         = $this->app->translator->translate('responses.repositories.recordUpdateSuccess');

        $ajaxResponse = new AjaxResponse($message, $templateContent);
        $ajaxResponse->setPageTitle($this->getFilesPageTitle($subdirectoryPath));
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getFilesPageTitle($subdirectoryPath));

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("my-files/settings", name="modules_my_files_settings")
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
        $ajaxResponse->setPageTitle($this->getFilesSettingsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @deprecated Not used anymore but fully functional
     *             Spinner on frontend cause a bit of problems with this solution
     *
     * @Route("download/file", name="download_file")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function download(Request $request)
    {

        if( !$request->request->has(MyFilesController::KEY_FILE_FULL_PATH)){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . MyFilesController::KEY_FILE_FULL_PATH;
            throw new Exception($message);
        }

        $fileFullPath = $request->request->get(MyFilesController::KEY_FILE_FULL_PATH);
        $file         = $this->fileDownloader->download($fileFullPath);

        $referer = $request->server->get('HTTP_REFERER');

        if( is_null($file) ){
            $response = $this->redirect($referer);

            if( empty($referer) ){
                $response = $this->redirectToRoute('/');
            }

            return $response;
        }

        return $file;
    }

    /**
     * @Route("/my-files/remove-file", name="my_files_remove_file", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeFileViaPost(Request $request): Response
    {
        $response = $this->filesHandler->removeFile($request);
        $message  = $response->getContent();

        $subdirectory    = $request->request->get(MyFilesController::KEY_SUBDIRECTORY);
        $templateContent = $this->renderCategoryTemplate($subdirectory, true, true)->getContent();

        if ($response->getStatusCode() == 200) {
            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message, $templateContent);
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     */
    private function renderSettingsTemplate(bool $ajaxRender = false): Response
    {
        $data = [
            'ajax_render' => $ajaxRender,
            'page_title'  => $this->getFilesSettingsPageTitle(),
        ];
        return $this->render(self::TWIG_TEMPLATE_MY_FILES_SETTINGS, $data);
    }

    /**
     * @param string|null $subdirectoryPath
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function renderCategoryTemplate(? string $subdirectoryPath, bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {

        $uploadDir                   = Env::getFilesUploadDir();
        $subdirPathInModuleUploadDir = FileUploadController::getSubdirectoryPath($uploadDir, $subdirectoryPath);

        $moduleUploadDirName = FilesHandler::getModuleUploadDirForUploadPath($uploadDir);
        $moduleName          = FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME[$moduleUploadDirName];

        if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($subdirPathInModuleUploadDir, LockedResource::TYPE_DIRECTORY, $moduleName)){
            return $this->redirect('/');
        }

        if( !file_exists($subdirPathInModuleUploadDir) ){

            $message = $this->app->translator->translate('flash.filesController.folderDoesNotExist') . $subdirectoryPath;
            $this->addFlash('danger', $message );

            return $this->redirectToRoute('upload_fine_upload');
        }

        if (empty($subdirectoryPath)) {
            $files = $this->controllers->getMyFilesController()->getMainFolderFiles();
        } else {
            $files = $this->controllers->getMyFilesController()->getFilesFromSubdirectory($subdirectoryPath);
        }

        # count files in dir tree - disables button for folder removing on front
        $searchDir        = (empty($subdirectoryPath) ? $uploadDir : $subdirPathInModuleUploadDir);
        $filesCountInTree = FilesHandler::countFilesInTree($searchDir);

        # null only when DirectoryNotFoundException was thrown
        if ( is_null($files) ) {
            $message     = $this->app->translator->translate("responses.directories.subdirectoryDoesNotExistForThisModule");
            $redirectUrl = $this->generateUrl('modules_my_files');

            $this->app->addDangerFlash($message);
            return $this->redirect($redirectUrl);
        }

        $isMainDir = ( empty($subdirectoryPath) );
        $uploadPath = Env::getFilesUploadDir() . DIRECTORY_SEPARATOR . $subdirectoryPath;

        $moduleData = $this->controllers->getModuleDataController()->getOneByRecordTypeModuleAndRecordIdentifier(
            ModuleData::RECORD_TYPE_DIRECTORY,
            ModulesController::MODULE_NAME_FILES,
            $uploadPath
        );

        $data = [
            'ajax_render'           => $ajaxRender,
            'files'                 => $files,
            'subdirectory_path'     => $subdirectoryPath,
            'files_count_in_tree'   => $filesCountInTree,
            'upload_module_dir'     => MyFilesController::TARGET_UPLOAD_DIR,
            'is_main_dir'           => $isMainDir,
            'module_data'           => $moduleData,
            'upload_path'           => $uploadPath,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getFilesPageTitle($subdirectoryPath),
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_FILES, $data);
    }

    /**
     * Will return the page title for files page
     *
     * @param string $subdirectoryPath
     * @return string
     */
    private function getFilesPageTitle(string $subdirectoryPath): string
    {
        $pageTitle = $this->app->translator->translate(
            'files.title',
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
    private function getFilesSettingsPageTitle(): string
    {
        return $this->app->translator->translate('files.settings.title');
    }


}