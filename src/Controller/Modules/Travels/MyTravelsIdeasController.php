<?php

namespace App\Controller\Modules\Travels;

use App\Controller\Core\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyTravelsIdeasController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @return mixed
     */
    public function getAllCategories(){
        return $this->app->repositories->myTravelsIdeasRepository->getAllCategories();
    }

}
