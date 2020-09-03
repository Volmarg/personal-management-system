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
     * @param string $file_path
     * @return string
     */
    public static function stripUploadDirectoryFromFilePathFront(string $file_path): string
    {
        $match = "#^" . Env::getUploadDir() . "/#";

        if( preg_match($match, $file_path) ){
            $file_path = preg_replace("#" . Env::getUploadDir() . "/#", "", $file_path , 1);
        }

        return $file_path;
    }

}