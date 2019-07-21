<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class EnvController extends AbstractController {


    public static function getUploadDir() {
        return $_ENV['UPLOAD_DIR'];
    }

    public static function getImagesUploadDir() {
        return $_ENV['IMAGES_UPLOAD_DIR'];
    }

    public static function getFilesUploadDir() {
        return $_ENV['FILES_UPLOAD_DIR'];
    }


}
