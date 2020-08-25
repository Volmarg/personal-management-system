<?php

namespace App\Controller\Modules\Travels;

use App\Controller\Core\Application;
use Doctrine\DBAL\DBALException;
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
     * @param bool $include_empty
     * @return mixed
     * @throws DBALException
     */
    public function getAllCategories(bool $include_empty = false){
        return $this->app->repositories->myTravelsIdeasRepository->getAllCategories($include_empty);
    }

}
