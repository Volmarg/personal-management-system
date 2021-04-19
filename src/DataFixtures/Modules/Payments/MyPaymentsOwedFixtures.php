<?php

namespace App\DataFixtures\Modules\Payments;

use App\DataFixtures\Providers\Modules\PaymentsOwed;
use App\Entity\Modules\Payments\MyPaymentsOwed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MyPaymentsOwedFixtures extends Fixture
{

    /**
     * Factory $faker
     */
    private $faker;

    public function __construct() {
        $this->faker = Factory::create('en');
    }

    public function load(ObjectManager $manager)
    {

        foreach ( PaymentsOwed::ALL_OWED_MONEY as $data ) {

            $target   = $data[PaymentsOwed::KEY_TARGET];
            $amount   = $data[PaymentsOwed::KEY_AMOUNT];
            $info     = $data[PaymentsOwed::KEY_INFO];
            $date     = $data[PaymentsOwed::KEY_DATE];
            $owedByMe = $data[PaymentsOwed::KEY_OWED_BY_ME];
            $currency = $data[PaymentsOwed::KEY_CURRENCY];

            $moneyOwed = new MyPaymentsOwed();
            $moneyOwed->setTarget($target);
            $moneyOwed->setAmount($amount);
            $moneyOwed->setInformation($info);
            $moneyOwed->setDate($date);
            $moneyOwed->setOwedByMe($owedByMe);
            $moneyOwed->setCurrency($currency);

            $manager->persist($moneyOwed);
        }

        $manager->flush();
    }

}
