<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsIncome;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyPaymentsIncomeController extends AbstractController
{

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * Returns all not deleted entities
     *
     * @return MyPaymentsIncome[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myPaymentsIncomeRepository->getAllNotDeleted();
    }

    /**
     * Returns all incomes summed up for each month in year
     *
     * @return array
     * @throws Exception
     */
    public function getAllNotDeletedSummedByYearAndMonth(): array
    {
        return $this->app->repositories->myPaymentsIncomeRepository->getAllNotDeletedSummedByYearAndMonth();
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsIncome|null
     */
    public function findOneById(int $id): ?MyPaymentsIncome
    {
        return $this->app->repositories->myPaymentsIncomeRepository->findOneById($id);
    }
}
