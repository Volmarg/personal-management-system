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


    public function fetchAllDateGroupsForYear(string $year)
    {
        return $this->app->repositories->myPaymentsMonthlyRepository->fetchAllDateGroupsForYear($year);
    }

    /**
     * @param string $year
     * @return mixed[]
     */
    public function getPaymentsByTypesForYear(string $year): array
    {
        return $this->app->repositories->myPaymentsMonthlyRepository->getPaymentsByTypesForYear($year);
    }


    /**
     * Will return all not deleted entities
     *
     * @param string $year
     * @return array
     */
    public function getAllNotDeletedForYear(string $year): array
    {
        return $this->app->repositories->myPaymentsMonthlyRepository->getAllNotDeletedForYear($year);
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
