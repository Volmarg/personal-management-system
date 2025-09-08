<?php

namespace App\DataFixtures\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsIncome;
use App\Repository\Modules\Payments\MyPaymentsMonthlyRepository;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\DBAL\DBALException;
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
     * @throws DBALException
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {

        $uniqueDates = $this->paymentsMonthlyRepository->getUniqueDatesFromPayments();
        foreach( $uniqueDates as $date ){

            $dateTime = new DateTime($date);
            $amount   = rand(1900, 3200);

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
