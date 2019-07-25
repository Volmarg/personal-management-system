<?php

namespace App\Controller\Modules\Images;

use App\Controller\Utils\Env;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Annotation\Route;

class MyImagesController extends AbstractController {

    const TWIG_TEMPLATE_MY_IMAGES = 'modules/my-images/my-images.html.twig';

    /**
     * @var Finder $finder
     */
    private $finder;

    public function __construct() {
        $this->finder = new Finder();
    }

    /**
     * @Route("my-images/dir/{subdirectory?}", name="modules_my_images")
     * @param string|null $subdirectory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayImages(? string $subdirectory) {

        if (empty($subdirectory)) {
            $all_images_paths = $this->getAllImages();
        } else {
            $all_images_paths = $this->getImagesFromCategory($subdirectory);
        }

        $data = [
            'ajax_render'       => false,
            'all_images_paths'  => $all_images_paths
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_IMAGES, $data);
    }

    private function getImagesFromCategory(string $subdirectory) {
        $upload_dir       = Env::getImagesUploadDir();
        $all_images_paths = [];
        $searchDir        = ( empty($subdirectory) ? $upload_dir : $upload_dir . '/' . $subdirectory);

        $this->finder->files()->in($searchDir);

        foreach ($this->finder as $image) {
            $all_images_paths[] = $image->getPathname();
        }

        return $all_images_paths;
    }

    private function getAllImages() {
        $all_images_paths = $this->getImagesFromCategory('');

        return $all_images_paths;
    }

}
