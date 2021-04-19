<?php

namespace App\DataFixtures\Modules\Payments;

use App\DataFixtures\Providers\Modules\PaymentsSettings;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MyPaymentsSettingsFixtures extends Fixture implements OrderedFixtureInterface
{

    /**
     * Factory $faker
     */
    private $faker;

    const MULTIPLIER = 4.56;

    const SETTING_NAME = 'type';

    public function __construct() {
        $this->faker = Factory::create('en');

    }

    public function load(ObjectManager $manager)
    {

        $currencyMultiplier = new MyPaymentsSettings();
        $currencyMultiplier->setName('currency_multiplier');
        $currencyMultiplier->setValue(static::MULTIPLIER);

        $manager->persist($currencyMultiplier);

        foreach ( PaymentsSettings::CATEGORIES_NAMES as $categoryName ) {

            $paymentType = new MyPaymentsSettings();
            $paymentType->setName(static::SETTING_NAME);
            $paymentType->setValue($categoryName);

            $manager->persist($paymentType);
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
