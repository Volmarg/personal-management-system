<?php

namespace App\DataFixtures\Modules\Payments;

use App\DataFixtures\Providers\Modules\PaymentsBills;
use App\Entity\Modules\Payments\MyPaymentsBills;
use App\Entity\Modules\Payments\MyPaymentsBillsItems;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
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

            $startDate     = new \DateTime($data[PaymentsBills::KEY_BILL_END_DATE]);
            $endDate       = new \DateTime($data[PaymentsBills::KEY_BILL_END_DATE]);
            $name          = $data[PaymentsBills::KEY_BILL_NAME];
            $information   = $data[PaymentsBills::KEY_BILL_INFORMATION];
            $plannedAmount = $data[PaymentsBills::KEY_BILL_PLANNED_AMOUNT];

            $bill = new MyPaymentsBills();
            $bill->setStartDate($startDate);
            $bill->setEndDate($endDate);
            $bill->setName($name);
            $bill->setInformation($information);
            $bill->setPlannedAmount($plannedAmount);

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

            $date      = new \DateTime($data[PaymentsBills::KEY_BILL_ITEM_DATE]);
            $name      = $data[PaymentsBills::KEY_BILL_ITEM_NAME];
            $amount    = $data[PaymentsBills::KEY_BILL_ITEM_AMOUNT];
            $billName  = $data[PaymentsBills::KEY_BILL_ITEM_BILL_NAME];

            $bills = $manager->getRepository(MyPaymentsBills::class)->findBy(['name' => $billName]);
            $bill  = reset($bills);

            $billItem = new MyPaymentsBillsItems();
            $billItem->setDate($date);
            $billItem->setAmount($amount);
            $billItem->setName($name);
            $billItem->setBill($bill);

            $manager->persist($billItem);
        }

        $manager->flush();
    }

}
