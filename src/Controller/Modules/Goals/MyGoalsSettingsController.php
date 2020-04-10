<?php

namespace App\Controller\Modules\Goals;

use App\Controller\Utils\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyGoalsSettingsController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app  = $app;
    }

}
