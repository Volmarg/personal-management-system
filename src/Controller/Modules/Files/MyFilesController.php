<?php

namespace App\Controller\Modules\Files;

use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Env;
use App\Services\FileDownloader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var FileDownloader $file_downloader
     */
    private $file_downloader;

    public function __construct(FileDownloader $file_downloader) {
        $this->finder           = new Finder();
        $this->file_downloader  = $file_downloader;
    }

    /**
     * @Route("my-files/dir/{subdirectory?}", name="modules_my_files")
     * @param string|null $subdirectory
     * @return Response
     */
    public function displayImages(? string $subdirectory) {

        if (empty($subdirectory)) {
            $files = $this->getAllFiles();
        } else {
            $files = $this->getFilesFromSubdirectory($subdirectory);
        }

        $data = [
            'ajax_render'       => false,
            'files'             => $files
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

        $this->finder->files()->in($searchDir);

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

    private function getAllFiles() {
        $all_files = $this->getFilesFromSubdirectory('');

        return $all_files;
    }

}
