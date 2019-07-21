<?php

namespace App\Controller\Modules\Images;

use App\Controller\EnvController;
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
     * @Route("my-images", name="modules_my_images")
     */
    public function index() {

        $all_images_paths = $this->getAllImages();

        $data = [
            'ajax_render'       => false,
            'all_images_paths'  => $all_images_paths
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_IMAGES, $data);
    }

    private function getImagesFromCategory(string $categoryName) {
        // Refactor getAllImages here
    }

    private function getAllImages() {
        $upload_dir       = EnvController::getImagesUploadDir();
        $all_images_paths = [];

        $this->finder->in($upload_dir);

        foreach ($this->finder as $image) {
            $all_images_paths[] = $image->getPathname();
        }

        return $all_images_paths;

    }

}
