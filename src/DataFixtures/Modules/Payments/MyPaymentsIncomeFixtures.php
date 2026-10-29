<?php

namespace App\DataFixtures\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsIncome;
use App\Repository\Modules\Payments\MyPaymentsMonthlyRepository;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;

class MyPaymentsIncomeFixtures extends Fixture implements OrderedFixtureInterface
{

    const CURRENCY_EUR = "EUR";

    /**
     * Factory $faker
     */
    private $faker;

    public function __construct(
        private readonly MyPaymentsMonthlyRepository $paymentsMonthlyRepository
    ) {
        $this->faker = Factory::create('en');
    }

    /**
     * @param ObjectManager $manager
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {

        $uniqueDates = $this->paymentsMonthlyRepository->getUniqueDatesFromPayments();
        foreach( $uniqueDates as $date ){

            $dateTime = new DateTime($date);
            $amount   = random_int(1900, 9650);

            $income = new MyPaymentsIncome();
            $income->setCurrency(self::CURRENCY_EUR);
            $income->setDate($dateTime);
            $income->setInformation("Monthly payment");
            $income->setAmount($amount);

            $manager->persist($income);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 18;
    }
}
