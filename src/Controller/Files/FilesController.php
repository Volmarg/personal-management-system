<?php


namespace App\Controller\Files;

use App\Controller\Utils\Application;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

class FilesController extends AbstractController {

    /*
     * TODO
     *  add file renaming
     *  add file removing
     *  add moving files between folders
     *      even the ones from files type to images types
     *  files download.... do I even need this.. can js handle it?
     */


    /**
     * @var Application $app
     */
    private $app;

    public function __construct(FileUploader $fileUploader, Application $app) {
        $this->app          = $app;
    }

}