<?php

namespace App\Controller\Modules\Dashboard;

use App\Controller\Core\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller {


    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

}
