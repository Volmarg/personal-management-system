<?php

namespace App\Controller\Modules\Issues;

use App\Controller\Core\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyIssuesController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

}