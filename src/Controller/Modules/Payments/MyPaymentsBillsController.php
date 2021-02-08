<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsBills;
use App\Entity\Modules\Payments\MyPaymentsBillsItems;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyPaymentsBillsController extends AbstractController
{

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @param MyPaymentsBills[] $bills
     * @param MyPaymentsBillsItems[] $billsItems
     * @return array
     */
    public function buildAmountSummaries(array $bills, array $billsItems):array{

        $summary = [];

        foreach($bills as $bill){

            foreach($billsItems as $billItem){

                $billId        = $bill->getId();
                $billIdForItem = $billItem->getBill()->getId();

                if( $billId === $billIdForItem ){

                    $amount = $billItem->getAmount();

                    if( array_key_exists($billId, $summary) ){
                        $summary[$billId] = ( $summary[$billId] + $amount );
                    }else{
                        $summary[$billId] = $amount;
                    }

                }

            }

        }

        return $summary;
    }

    /**
     * @return MyPaymentsBills[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myPaymentsBillsRepository->getAllNotDeleted();
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsBills|null
     */
    public function findOneById(int $id): ?MyPaymentsBills
    {
        return $this->app->repositories->myPaymentsBillsRepository->findOneById($id);
    }
}
