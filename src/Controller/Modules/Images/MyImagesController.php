<?php

namespace App\Controller\Modules\Images;

use App\Controller\Utils\Env;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyImagesController extends AbstractController {

    const TWIG_TEMPLATE_MY_IMAGES = 'modules/my-images/my-images.html.twig';
    const KEY_FILE_NAME           = 'file_name';
    const KEY_FILE_FULL_PATH      = 'file_full_path';
    const MODULE_NAME             = 'My Images';
    const TARGET_TYPE             = 'images';

    /**
     * @var Finder $finder
     */
    private $finder;

    public function __construct() {
        $this->finder = new Finder();
        $this->finder->depth('== 0');

    }

    /**
     * @Route("my-images/dir/{subdirectory?}", name="modules_my_images")
     * @param string|null $subdirectory
     * @return Response
     */
    public function displayImages(? string $subdirectory) {

        if (empty($subdirectory)) {
            $all_images = $this->getMainFolderImages();
        } else {
            $subdirectory   = urldecode($subdirectory);
            $all_images     = $this->getImagesFromCategory($subdirectory);
        }

        $data = [
            'ajax_render' => false,
            'all_images'  => $all_images
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_IMAGES, $data);
    }

    private function getImagesFromCategory(string $subdirectory) {
        $upload_dir       = Env::getImagesUploadDir();
        $all_images       = [];
        $searchDir        = ( empty($subdirectory) ? $upload_dir : $upload_dir . '/' . $subdirectory);

        $this->finder->files()->in($searchDir);

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
