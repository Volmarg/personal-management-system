<?php

namespace App\DataFixtures\Modules\Payments;

use App\DataFixtures\Providers\Modules\PaymentsBills;
use App\Entity\Modules\Payments\MyPaymentsBills;
use App\Entity\Modules\Payments\MyPaymentsBillsItems;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyPaymentsBillsFixtures extends Fixture
{

    /**
     * Factory $faker
     */
    private $faker;

    public function __construct() {
        $this->faker = Factory::create('en');
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $this->loadBills($manager);
        $this->loadBillsItems($manager);

    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function loadBills(ObjectManager $manager){

        foreach ( PaymentsBills::ALL_BILLS as $data ) {

            $start_date     = new \DateTime($data[PaymentsBills::KEY_BILL_END_DATE]);
            $end_date       = new \DateTime($data[PaymentsBills::KEY_BILL_END_DATE]);
            $name           = $data[PaymentsBills::KEY_BILL_NAME];
            $information    = $data[PaymentsBills::KEY_BILL_INFORMATION];
            $planned_amount = $data[PaymentsBills::KEY_BILL_PLANNED_AMOUNT];

            $bill = new MyPaymentsBills();
            $bill->setStartDate($start_date);
            $bill->setEndDate($end_date);
            $bill->setName($name);
            $bill->setInformation($information);
            $bill->setPlannedAmount($planned_amount);

            $manager->persist($bill);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function loadBillsItems(ObjectManager $manager){

        foreach ( PaymentsBills::ALL_BILLS_ITEMS as $data ) {

            $date       = new \DateTime($data[PaymentsBills::KEY_BILL_ITEM_DATE]);
            $name       = $data[PaymentsBills::KEY_BILL_ITEM_NAME];
            $amount     = $data[PaymentsBills::KEY_BILL_ITEM_AMOUNT];
            $bill_name  = $data[PaymentsBills::KEY_BILL_ITEM_BILL_NAME];

            $bills = $manager->getRepository(MyPaymentsBills::class)->findBy(['name' => $bill_name]);
            $bill  = reset($bills);

            $bill_item = new MyPaymentsBillsItems();
            $bill_item->setDate($date);
            $bill_item->setAmount($amount);
            $bill_item->setName($name);
            $bill_item->setBill($bill);

            $manager->persist($bill_item);
        }

        $manager->flush();
    }

}
