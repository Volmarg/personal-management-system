<?php


namespace App\Action\Files;


use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FileUploadAction extends AbstractController {


    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;


    public function __construct(Controllers $controllers, Application $app) {
        $this->app = $app;
        $this->controllers = $controllers;
    }

}