<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsProduct;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyPaymentsProductsController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Will return all not deleted entries
     *
     * @return MyPaymentsProduct[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myPaymentsProductRepository->getAllNotDeleted();
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsProduct|null
     */
    public function findOneById(int $id): ?MyPaymentsProduct
    {
        return $this->app->repositories->myPaymentsProductRepository->findOneById($id);
    }
}
