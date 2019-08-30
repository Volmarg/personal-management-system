<?php

namespace App\Controller\Modules\Images;

use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Env;
use App\Services\FilesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyImagesController extends AbstractController {

    const TWIG_TEMPLATE_MY_IMAGES = 'modules/my-images/my-images.html.twig';
    const KEY_FILE_NAME           = 'file_name';
    const KEY_FILE_FULL_PATH      = 'file_full_path';
    const MODULE_NAME             = 'My Images';
    const TARGET_UPLOAD_DIR       = 'images';

    /**
     * @var Finder $finder
     */
    private $finder;

    public function __construct() {
        $this->finder = new Finder();
        $this->finder->depth('== 0');

    }

    /**
     * @Route("my-images/dir/{encoded_subdirectory_path?}", name="modules_my_images")
     * @param string|null $encoded_subdirectory_path
     * @return Response
     */
    public function displayImages(? string $encoded_subdirectory_path) {

        $module_upload_dir                      = Env::getImagesUploadDir();
        $decoded_subdirectory_path              = urldecode($encoded_subdirectory_path);
        $subdirectory_path_in_module_upload_dir = FileUploadController::getSubdirectoryPath($module_upload_dir, $decoded_subdirectory_path);

        if( !file_exists($subdirectory_path_in_module_upload_dir) ){
            $subdirectory_name = basename($decoded_subdirectory_path);
            $this->addFlash('danger', "Folder '{$subdirectory_name} does not exist.");
            return $this->redirectToRoute('upload');
        }

        if (empty($decoded_subdirectory_path)) {
            $all_images                 = $this->getMainFolderImages();
        } else {
            $decoded_subdirectory_path   = urldecode($decoded_subdirectory_path);
            $all_images                  = $this->getImagesFromCategory($decoded_subdirectory_path);
        }

        # count files in dir tree - disables button for folder removing on front
        $searchDir              = (empty($decoded_subdirectory_path) ? $module_upload_dir : $subdirectory_path_in_module_upload_dir);
        $files_count_in_tree    = FilesHandler::countFilesInTree($searchDir);

        $data = [
            'ajax_render'           => false,
            'all_images'            => $all_images,
            'subdirectory_path'     => $decoded_subdirectory_path,
            'files_count_in_tree'   => $files_count_in_tree,
            'upload_module_dir'     => static::TARGET_UPLOAD_DIR
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_IMAGES, $data);
    }

    private function getImagesFromCategory(string $subdirectory) {
        $upload_dir       = Env::getImagesUploadDir();
        $all_images       = [];
        $search_dir       = ( empty($subdirectory) ? $upload_dir : $upload_dir . '/' . $subdirectory);

        $this->finder->files()->in($search_dir);

        foreach ($this->finder as $image) {
            $all_images[] = [
                static::KEY_FILE_FULL_PATH => $image->getPathname(),
                static::KEY_FILE_NAME      => $image->getFilename()
            ];
        }

        return $all_images;
    }

    private function getMainFolderImages() {
        $all_images_paths = $this->getImagesFromCategory('');

        return $all_images_paths;
    }

}
