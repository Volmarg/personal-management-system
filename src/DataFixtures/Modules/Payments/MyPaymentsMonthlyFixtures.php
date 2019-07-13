<?php

namespace App\DataFixtures\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsMonthly;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyPaymentsMonthlyFixtures extends Fixture implements OrderedFixtureInterface
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
        /**
         * @var MyPaymentsSettingsRepository $payments_settings_repository
         */
        $payments_settings_repository = $manager->getRepository(MyPaymentsSettings::class);
        $payments_types               = $payments_settings_repository->getAllPaymentsTypes();

        for($x = 0; $x <= 50; $x++){

            $index                    = array_rand($payments_types);
            $payment_type             = $payments_types[$index];
            $description              = $this->faker->sentence;
            $dateTime                 = $this->faker->dateTimeBetween('-5months', '+2months');
            $date                     = $dateTime->format('d-m-Y');
            $money                    = $this->faker->randomFloat(2, 2, 150);

            $monthlyPayment = new MyPaymentsMonthly();
            $monthlyPayment->setType($payment_type);
            $monthlyPayment->setDescription($description);
            $monthlyPayment->setDate($date);
            $monthlyPayment->setMoney($money);

            $manager->persist($monthlyPayment);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 5;
    }
}
