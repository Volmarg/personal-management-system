<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyRecurringPaymentsMonthlyController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MyRecurringPaymentMonthly|null
     */
    public function findOneById(int $id): ?MyRecurringPaymentMonthly
    {
        return $this->app->repositories->myRecurringPaymentMonthlyRepository->findOneById($id);
    }

    /**
     * Will return all not deleted records
     *
     * @param string $sort_by_column
     * @param string $sort_direction
     * @return MyRecurringPaymentMonthly[]
     */
    public function getAllNotDeleted(string $sort_by_column, string $sort_direction = "ASC")
    {
        return $this->app->repositories->myRecurringPaymentMonthlyRepository->getAllNotDeleted($sort_by_column, $sort_direction);
    }
}
