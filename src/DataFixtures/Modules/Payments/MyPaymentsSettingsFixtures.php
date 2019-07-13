<?php

namespace App\DataFixtures\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsSettings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyPaymentsSettingsFixtures extends Fixture implements OrderedFixtureInterface
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
        $multiplier = 4.13;

        $currency_multiplier = new MyPaymentsSettings();
        $currency_multiplier->setName('currency_multiplier');
        $currency_multiplier->setValue($multiplier);

        $manager->persist($currency_multiplier);

        for($x = 0; $x <= 7; $x++){

            $type_name  = $this->faker->word;

            $payment_type = new MyPaymentsSettings();
            $payment_type->setName('type');
            $payment_type->setValue($type_name);

            $manager->persist($payment_type);

        }


        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 4;
    }
}
