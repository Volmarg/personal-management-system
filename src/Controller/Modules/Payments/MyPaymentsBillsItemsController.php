<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsBillsItems;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyPaymentsBillsItemsController extends AbstractController
{

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @return MyPaymentsBillsItems[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myPaymentsBillsItemsRepository->getAllNotDeleted();
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsBillsItems|null
     */
    public function findOneById(int $id): ?MyPaymentsBillsItems
    {
        return $this->app->repositories->myPaymentsBillsItemsRepository->findOneById($id);
    }

}
