<?php


namespace App\Controller\Files;

use App\Controller\Core\Env;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FilesController extends AbstractController {

    /**
     * If the file_path has `upload` directory from front then it will be stripped
     * this is for example needed for generating further miniatures for uploaded files
     *
     * @param string $filePath
     * @return string
     */
    public static function stripUploadDirectoryFromFilePathFront(string $filePath): string
    {
        $match = "#^" . Env::getUploadDir() . "/#";

        if( preg_match($match, $filePath) ){
            $filePath = preg_replace("#" . Env::getUploadDir() . "/#", "", $filePath , 1);
        }

        return $filePath;
    }

}