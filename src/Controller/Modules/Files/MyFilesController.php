<?php

namespace App\Controller\Modules\Files;

use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Env;
use App\Services\FileDownloader;
use App\Services\FilesHandler;
use App\Services\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
     * @var FilesHandler $filesHandler
     */
    private $filesHandler;

    /**
     * @var FilesTagsController $files_tags_controller
     */
    private $files_tags_controller;

    public function __construct(FileDownloader $file_downloader, FilesHandler $filesHandler, FilesTagsController $files_tags_controller) {
        $this->finder           = new Finder();
        $this->finder->depth('== 0');

        $this->file_downloader       = $file_downloader;
        $this->filesHandler          = $filesHandler;
        $this->files_tags_controller = $files_tags_controller;

    }

    /**
     * @Route("my-files/dir/{encoded_subdirectory_path?}", name="modules_my_files")
     * @param string|null $encoded_subdirectory_path
     * @param Request $request
     * @return Response
     */
    public function displayFiles(? string $encoded_subdirectory_path, Request $request) {

        $ajax_render                      = false;
        $upload_dir                       = Env::getFilesUploadDir();
        $decoded_subdirectory_path        = urldecode($encoded_subdirectory_path);
        $subdir_path_in_module_upload_dir = FileUploadController::getSubdirectoryPath($upload_dir, $decoded_subdirectory_path);

        if( !file_exists($subdir_path_in_module_upload_dir) ){
            $this->addFlash('danger', "Folder '{$decoded_subdirectory_path} does not exist.");
            return $this->redirectToRoute('upload');
        }

        if (empty($decoded_subdirectory_path)) {
            $files                     = $this->getMainFolderFiles();
        } else {
            $decoded_subdirectory_path = urldecode($decoded_subdirectory_path);
            $files                     = $this->getFilesFromSubdirectory($decoded_subdirectory_path);
        }

        # count files in dir tree - disables button for folder removing on front
        $search_dir              = (empty($decoded_subdirectory_path) ? $upload_dir : $subdir_path_in_module_upload_dir);
        $files_count_in_tree    = FilesHandler::countFilesInTree($search_dir);

        # A bit dirty workaround
        if ($files instanceof RedirectResponse) {
            return $files;
        }

        if ( $request->isXmlHttpRequest() ) {
            $ajax_render = true;
        }

        $is_main_dir = ( empty($decoded_subdirectory_path) );

        $data = [
            'ajax_render'           => $ajax_render,
            'files'                 => $files,
            'subdirectory_path'     => $decoded_subdirectory_path,
            'files_count_in_tree'   => $files_count_in_tree,
            'upload_module_dir'     => static::TARGET_UPLOAD_DIR,
            'is_main_dir'           => $is_main_dir
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_FILES, $data);
    }

    /**
     * @Route("download/file", name="download_file")
     * @param Request $request
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function download(Request $request)
    {

        if( !$request->request->has(static::KEY_FILE_FULL_PATH)){
            throw new \Exception('Request is missing key: ' . static::KEY_FILE_FULL_PATH);
        }

        $file_full_path = $request->request->get(static::KEY_FILE_FULL_PATH);
        $file           = $this->file_downloader->download($file_full_path);

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

            $all_files[$index] = [
                static::KEY_FILE_NAME      => $file->getFilenameWithoutExtension(),
                static::KEY_FILE_SIZE      => $file->getSize(),
                static::KEY_FILE_EXTENSION => $file->getExtension(),
                static::KEY_FILE_FULL_PATH => $file->getPath() . '/' . $file->getFilename()
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
     * @throws \Exception
     */
    public function removeFileViaPost(Request $request) {
        $subdirectory = $request->request->get(static::KEY_SUBDIRECTORY);
        $this->filesHandler->removeFile($request);

        return $this->displayFiles($subdirectory, $request);
    }

    /**
     * Handles file renaming and tags updating
     * @Route("/api/my-files/update", name="my_files_update", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function update(Request $request){

        if (!$request->request->has(static::KEY_FILE_FULL_PATH)) {
            throw new \Exception('Missing request parameter named: ' . static::KEY_FILE_FULL_PATH);
        }

        $filepath = $_SERVER['DOCUMENT_ROOT'] . $request->request->get(static::KEY_FILE_FULL_PATH);

        $subdirectory = $request->request->get(static::KEY_SUBDIRECTORY);
        $tags_string  = $request->request->get(FileTagger::KEY_TAGS);

        $this->filesHandler->renameFile($request);
        $this->files_tags_controller->updateTags($tags_string, $filepath);

        return $this->displayFiles($subdirectory, $request);
    }

    /**
     * TODO: replace this with update() - this one is triggered when clicking the save() action on my-files
     * @Route("/my-files/rename-file", name="my_files_rename_file", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function renameFileViaPost(Request $request) {
        $subdirectory = $request->request->get(static::KEY_SUBDIRECTORY);
        $this->filesHandler->renameFile($request);

        return $this->displayFiles($subdirectory, $request);
    }


}
