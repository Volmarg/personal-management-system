<?php

namespace App\Controller\Modules\Files;

use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FileUploadController;
use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Controller\Utils\Env;
use App\Entity\FilesTags;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use App\Services\FileDownloader;
use App\Services\FilesHandler;
use App\Services\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyFilesController extends AbstractController
{

    const TWIG_TEMPLATE_MY_FILES = 'modules/my-files/my-files.html.twig';

    const KEY_FILE_NAME          = 'file_name';
    const KEY_FILE_SIZE          = 'file_size';
    const KEY_FILE_EXTENSION     = 'file_extension';
    const KEY_FILE_FULL_PATH     = 'file_full_path';
    const KEY_SUBDIRECTORY       = 'subdirectory';
    const MODULE_NAME            = 'My Files';
    const TARGET_UPLOAD_DIR      = 'files';

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
     * @var FilesTagsController $files_tags_controller
     */
    private $files_tags_controller;

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var FileTagger $file_tagger
     */
    private $file_tagger;

    public function __construct(FileDownloader $file_downloader, FilesHandler $files_handler, FilesTagsController $files_tags_controller, Application $app, FileTagger $file_tagger) {
        $this->finder = new Finder();
        $this->finder->depth('== 0');

        $this->file_downloader       = $file_downloader;
        $this->files_handler         = $files_handler;
        $this->files_tags_controller = $files_tags_controller;
        $this->app                   = $app;
        $this->file_tagger           = $file_tagger;

    }

    /**
     * @Route("my-files/dir/{encoded_subdirectory_path?}", name="modules_my_files")
     * @param string|null $encoded_subdirectory_path
     * @param Request $request
     * @return Response
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function displayFiles(? string $encoded_subdirectory_path, Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($encoded_subdirectory_path, false);
        }

        $template_content  = $this->renderCategoryTemplate($encoded_subdirectory_path, true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param string|null $encoded_subdirectory_path
     * @param bool $ajax_render
     * @return array|RedirectResponse|Response
     * @throws ExceptionDuplicatedTranslationKey
     */
    protected function renderCategoryTemplate(? string $encoded_subdirectory_path, bool $ajax_render = false) {

        $upload_dir                       = Env::getFilesUploadDir();
        $decoded_subdirectory_path        = urldecode($encoded_subdirectory_path);
        $subdirectory_path                = FilesHandler::trimFirstAndLastSlash($decoded_subdirectory_path);
        $subdir_path_in_module_upload_dir = FileUploadController::getSubdirectoryPath($upload_dir, $subdirectory_path);

        if( !file_exists($subdir_path_in_module_upload_dir) ){

            $message = $this->app->translator->translate('flash.filesController.folderDoesNotExist') . $subdirectory_path;
            $this->addFlash('danger', $message );

            return $this->redirectToRoute('upload');
        }

        if (empty($subdirectory_path)) {
            $files = $this->getMainFolderFiles();
        } else {
            $files = $this->getFilesFromSubdirectory($subdirectory_path);
        }

        # count files in dir tree - disables button for folder removing on front
        $search_dir             = (empty($subdirectory_path) ? $upload_dir : $subdir_path_in_module_upload_dir);
        $files_count_in_tree    = FilesHandler::countFilesInTree($search_dir);

        # A bit dirty workaround
        if ($files instanceof RedirectResponse) {
            return $files;
        }

        $is_main_dir = ( empty($subdirectory_path) );

        $data = [
            'ajax_render'           => $ajax_render,
            'files'                 => $files,
            'subdirectory_path'     => $subdirectory_path,
            'files_count_in_tree'   => $files_count_in_tree,
            'upload_module_dir'     => static::TARGET_UPLOAD_DIR,
            'is_main_dir'           => $is_main_dir
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_FILES, $data);
    }

    /**
     * @Route("download/file", name="download_file")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function download(Request $request)
    {

        if( !$request->request->has(static::KEY_FILE_FULL_PATH)){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_FILE_FULL_PATH;
            throw new Exception($message);
        }

        $file_full_path = $request->request->get(static::KEY_FILE_FULL_PATH);
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

    private function getFilesFromSubdirectory(string $subdirectory) {
        $upload_dir       = Env::getFilesUploadDir();
        $all_files        = [];
        $search_dir       = ( empty($subdirectory) ? $upload_dir : FileUploadController::getSubdirectoryPath($upload_dir, $subdirectory));

        try{
            $this->finder->files()->in($search_dir);

        }catch(DirectoryNotFoundException $de){

            $this->addFlash('danger', $de->getMessage());
            $redirect_url = $this->generateUrl('modules_my_files');

            return $this->redirect($redirect_url);
        }

        foreach ($this->finder as $index => $file) {

            $file_full_path = $file->getPath() . '/' . $file->getFilename();
            $file_tags      = $this->app->repositories->filesTagsRepository->getFileTagsEntityByFileFullPath($file_full_path);
            $tags_json      = ( $file_tags instanceof FilesTags ? $file_tags->getTags() : "" );

            $all_files[$index] = [
                static::KEY_FILE_NAME      => $file->getFilenameWithoutExtension(),
                static::KEY_FILE_SIZE      => $file->getSize(),
                static::KEY_FILE_EXTENSION => $file->getExtension(),
                static::KEY_FILE_FULL_PATH => $file_full_path,
                FileTagger::KEY_TAGS       => $tags_json
            ];

        }

        return $all_files;
    }

    private function getMainFolderFiles() {
        $all_files = $this->getFilesFromSubdirectory('');

        return $all_files;
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

        if ($response->getStatusCode() == 200) {
            return AjaxResponse::buildResponseForAjaxCall(200, $message);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * Handles file renaming and tags updating
     * @Route("/api/my-files/update", name="my_files_update", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function update(Request $request){

        if (!$request->request->has(static::KEY_FILE_FULL_PATH)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_FILE_FULL_PATH;
            throw new Exception($message);
        }

        $subdirectory = $request->request->get(static::KEY_SUBDIRECTORY);
        $tags_string  = $request->request->get(FileTagger::KEY_TAGS);

        $update_file_path_for_tags = function ($curr_relative_filepath, $new_relative_file_path) use($tags_string) {
            $this->file_tagger->updateFilePath($curr_relative_filepath, $new_relative_file_path);
            $this->files_tags_controller->updateTags($tags_string, $new_relative_file_path);
        };

        $this->files_handler->renameFileViaRequest($request, $update_file_path_for_tags);
        return $this->displayFiles($subdirectory, $request);
    }

}
