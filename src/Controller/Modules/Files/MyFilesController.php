<?php

namespace App\Controller\Modules\Files;

use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Env;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyFilesController extends AbstractController
{

    const TWIG_TEMPLATE_MY_FILES = 'modules/my-files/my-files.html.twig';

    const KEY_FILE_NAME          = 'file_name';
    const KEY_FILE_SIZE          = 'file_size';
    const KEY_FILE_EXTENSION     = 'file_extension';

    /**
     * @var Finder $finder
     */
    private $finder;

    public function __construct() {
        $this->finder = new Finder();
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

    private function getFilesFromSubdirectory(string $subdirectory) {
        $upload_dir       = Env::getFilesUploadDir();
        $all_files        = [];
        $searchDir        = ( empty($subdirectory) ? $upload_dir : FileUploadController::getSubdirectoryPath($upload_dir, $subdirectory));

        $this->finder->files()->in($searchDir);

        foreach ($this->finder as $index => $file) {
            $all_files[$index][static::KEY_FILE_NAME]       = $file->getFilenameWithoutExtension();
            $all_files[$index][static::KEY_FILE_SIZE]       = $file->getSize();
            $all_files[$index][static::KEY_FILE_EXTENSION]  = $file->getExtension();
        }

        return $all_files;
    }

    private function getAllFiles() {
        $all_files = $this->getFilesFromSubdirectory('');

        return $all_files;
    }

}
