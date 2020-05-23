<?php
namespace App\Action\Modules\Files;

use App\Controller\Files\FileUploadController;
use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Env;
use App\Entity\System\LockedResource;
use App\Services\Files\FileDownloader;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyFilesAction extends AbstractController {

    const TWIG_TEMPLATE_MY_FILES          = 'modules/my-files/my-files.html.twig';
    const TWIG_TEMPLATE_MY_FILES_SETTINGS = 'modules/my-files/settings.html.twig';

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var FileDownloader $file_downloader
     */
    private $file_downloader;

    /**
     * @var FilesHandler $files_handler
     */
    private $files_handler;

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var FileTagger $file_tagger
     */
    private $file_tagger;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(
        FileDownloader $file_downloader,
        FilesHandler   $files_handler,
        Application    $app,
        FileTagger     $file_tagger,
        Controllers    $controllers
    ) {
        $this->finder = new Finder();
        $this->finder->depth('== 0');

        $this->file_downloader = $file_downloader;
        $this->files_handler   = $files_handler;
        $this->app             = $app;
        $this->file_tagger     = $file_tagger;
        $this->controllers     = $controllers;
    }

    /**
     * Handles file renaming and tags updating
     * @Route("/api/my-files/update", name="my_files_update", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function update(Request $request){

        if (!$request->request->has(MyFilesController::KEY_FILE_FULL_PATH)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . MyFilesController::KEY_FILE_FULL_PATH;
            throw new Exception($message);
        }

        $subdirectory = $request->request->get(MyFilesController::KEY_SUBDIRECTORY);
        $tags_string  = $request->request->get(FileTagger::KEY_TAGS);

        $update_file_path = function ($curr_relative_filepath, $new_relative_file_path) use($tags_string) {
            $this->file_tagger->updateFilePath($curr_relative_filepath, $new_relative_file_path);
            $this->controllers->getFilesTagsController()->updateTags($tags_string, $new_relative_file_path);
            $this->app->repositories->lockedResourceRepository->updatePath($curr_relative_filepath, $new_relative_file_path);
        };

        $this->files_handler->renameFileViaRequest($request, $update_file_path);
        return $this->displayFiles($subdirectory, $request);
    }

    /**
     * @Route("my-files/dir/{encoded_subdirectory_path?}", name="modules_my_files")
     * @param string|null $encoded_subdirectory_path
     * @param Request $request
     * @return Response
     * 
     */
    public function displayFiles(? string $encoded_subdirectory_path, Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($encoded_subdirectory_path, false);
        }

        $template_content  = $this->renderCategoryTemplate($encoded_subdirectory_path, true)->getContent();
        $message           = $this->app->translator->translate('responses.repositories.recordUpdateSuccess');
        return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
    }

    /**
     * @Route("my-files/settings", name="modules_my_files_settings")
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

        $file_full_path = $request->request->get(MyFilesController::KEY_FILE_FULL_PATH);
        $file           = $this->file_downloader->download($file_full_path);

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
    public function removeFileViaPost(Request $request) {
        $response = $this->files_handler->removeFile($request);
        $message  = $response->getContent();

        $subdirectory     = $request->request->get(MyFilesController::KEY_SUBDIRECTORY);
        $template_content = $this->renderCategoryTemplate($subdirectory, true, true)->getContent();

        //todo: do the same with transfer
        if ($response->getStatusCode() == 200) {
            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message, $template_content);
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
        return $this->render(self::TWIG_TEMPLATE_MY_FILES_SETTINGS, $data);
    }

    /**
     * @param string|null $encoded_subdirectory_path
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     *
     * @throws Exception
     */
    private function renderCategoryTemplate(? string $encoded_subdirectory_path, bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false): Response
    {

        $upload_dir                       = Env::getFilesUploadDir();
        $decoded_subdirectory_path        = urldecode($encoded_subdirectory_path);
        $subdirectory_path                = FilesHandler::trimFirstAndLastSlash($decoded_subdirectory_path);
        $subdir_path_in_module_upload_dir = FileUploadController::getSubdirectoryPath($upload_dir, $subdirectory_path);

        $module_upload_dir_name = FilesHandler::getModuleUploadDirForUploadPath($upload_dir);
        $module_name            = FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME[$module_upload_dir_name];

        if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($subdir_path_in_module_upload_dir, LockedResource::TYPE_DIRECTORY, $module_name)         ){
            return $this->redirect('/');
        }

        if( !file_exists($subdir_path_in_module_upload_dir) ){

            $message = $this->app->translator->translate('flash.filesController.folderDoesNotExist') . $subdirectory_path;
            $this->addFlash('danger', $message );

            return $this->redirectToRoute('upload');
        }

        if (empty($subdirectory_path)) {
            $files = $this->controllers->getMyFilesController()->getMainFolderFiles();
        } else {
            $files = $this->controllers->getMyFilesController()->getFilesFromSubdirectory($subdirectory_path);
        }

        # count files in dir tree - disables button for folder removing on front
        $search_dir          = (empty($subdirectory_path) ? $upload_dir : $subdir_path_in_module_upload_dir);
        $files_count_in_tree = FilesHandler::countFilesInTree($search_dir);

        # null only when DirectoryNotFoundException was thrown
        if ( is_null($files) ) {
            $message      = $this->app->translator->translate("responses.directories.subdirectoryDoesNotExistForThisModule");
            $redirect_url = $this->generateUrl('modules_my_files');

            $this->app->addDangerFlash($message);
            return $this->redirect($redirect_url);
        }

        $is_main_dir = ( empty($subdirectory_path) );

        $data = [
            'ajax_render'           => $ajax_render,
            'files'                 => $files,
            'subdirectory_path'     => $subdirectory_path,
            'files_count_in_tree'   => $files_count_in_tree,
            'upload_module_dir'     => MyFilesController::TARGET_UPLOAD_DIR,
            'is_main_dir'           => $is_main_dir,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_FILES, $data);
    }

}