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
     * @return MyRecurringPaymentMonthly[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myRecurringPaymentMonthlyRepository->getAllNotDeleted();
    }
}
