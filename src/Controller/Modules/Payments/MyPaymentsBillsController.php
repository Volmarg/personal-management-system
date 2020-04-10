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
     * @param MyPaymentsBillsItems[] $bills_items
     * @return array
     */
    public function buildAmountSummaries(array $bills, array $bills_items):array{

        $summary = [];

        foreach($bills as $bill){

            foreach($bills_items as $bill_item){

                $bill_id            = $bill->getId();
                $bill_id_for_item   = $bill_item->getBill()->getId();

                if( $bill_id === $bill_id_for_item ){

                    $amount = $bill_item->getAmount();

                    if( array_key_exists($bill_id, $summary) ){
                        $summary[$bill_id] = ( $summary[$bill_id] + $amount );
                    }else{
                        $summary[$bill_id] = $amount;
                    }

                }

            }

        }

        return $summary;
    }

}
