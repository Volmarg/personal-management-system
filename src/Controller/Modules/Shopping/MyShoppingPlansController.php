<?php

namespace App\Controller\Modules\Shopping;

use App\Controller\Core\Application;
use App\Entity\Modules\Shopping\MyShoppingPlans;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyShoppingPlansController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MyShoppingPlans|null
     */
    public function findOneById(int $id): ?MyShoppingPlans
    {
        return $this->app->repositories->myShoppingPlansRepository->findOneById($id);
    }

}
