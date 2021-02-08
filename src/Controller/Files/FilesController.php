<?php


namespace App\Controller\Files;

use App\Controller\Core\Application;
use App\Controller\Core\Env;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FilesController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

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