<?php

namespace App\DataFixtures\Modules\Payments;

use App\DataFixtures\Providers\Modules\PaymentsOwed;
use App\Entity\Modules\Payments\MyPaymentsOwed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
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

            $target     = $data[PaymentsOwed::KEY_TARGET];
            $amount     = $data[PaymentsOwed::KEY_AMOUNT];
            $info       = $data[PaymentsOwed::KEY_INFO];
            $date       = $data[PaymentsOwed::KEY_DATE];
            $owed_by_me = $data[PaymentsOwed::KEY_OWED_BY_ME];

            $money_owed = new MyPaymentsOwed();
            $money_owed->setTarget($target);
            $money_owed->setAmount($amount);
            $money_owed->setInformation($info);
            $money_owed->setDate($date);
            $money_owed->setOwedByMe($owed_by_me);

            $manager->persist($money_owed);
        }

        $manager->flush();
    }

}
