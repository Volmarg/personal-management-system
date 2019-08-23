<?php

namespace App\Controller\Modules\Files;

use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Env;
use App\Services\FileDownloader;
use App\Services\FilesHandler;
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

    public function __construct(FileDownloader $file_downloader, FilesHandler $filesHandler) {
        $this->finder           = new Finder();
        $this->finder->depth('== 0');

        $this->file_downloader  = $file_downloader;
        $this->filesHandler     = $filesHandler;

    }

    /**
     * @Route("my-files/dir/{subdirectory?}", name="modules_my_files")
     * @param string|null $subdirectory
     * @param Request $request
     * @return Response
     */
    public function displayImages(? string $subdirectory, Request $request) {

        $ajax_render = false;

        if (empty($subdirectory)) {
            $files = $this->getMainFolderFiles();
        } else {
            $files = $this->getFilesFromSubdirectory($subdirectory);
        }

        # A bit dirty workaround
        if ($files instanceof RedirectResponse) {
            return $files;
        }

        if ( $request->isXmlHttpRequest() ) {
            $ajax_render = true;
        }

        $data = [
            'ajax_render'       => $ajax_render,
            'files'             => $files,
            'subdirectory'      => $subdirectory
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
        $searchDir        = ( empty($subdirectory) ? $upload_dir : FileUploadController::getSubdirectoryPath($upload_dir, $subdirectory));

        try{
            $this->finder->files()->in($searchDir);

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

        return $this->displayImages($subdirectory, $request);
    }

    /**
     * @Route("/my-files/rename-file", name="my_files_rename_file", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function renameFileViaPost(Request $request) {
        $subdirectory = $request->request->get(static::KEY_SUBDIRECTORY);
        $this->filesHandler->renameFile($request);

        return $this->displayImages($subdirectory, $request);
    }


}
