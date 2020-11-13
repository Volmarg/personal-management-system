<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsMonthly;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyPaymentsMonthlyController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }


    public function fetchAllDateGroups()
    {
        return $this->app->repositories->myPaymentsMonthlyRepository->fetchAllDateGroups();
    }

    public function getPaymentsByTypes()
    {
        return $this->app->repositories->myPaymentsMonthlyRepository->getPaymentsByTypes();
    }


    /**
     * Will return all not deleted entities
     *
     * @return array
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myPaymentsMonthlyRepository->getAllNotDeleted();
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsMonthly|null
     */
    public function findOneById(int $id): ?MyPaymentsMonthly
    {
        return $this->app->repositories->myPaymentsMonthlyRepository->findOneById($id);
    }
}
