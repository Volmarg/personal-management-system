<?php

namespace App\DataFixtures\Modules\Goals;

use App\DataFixtures\Providers\Modules\Goals;
use App\Entity\Modules\Goals\MyGoalsPayments;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MyGoalsPaymentsFixtures extends Fixture implements OrderedFixtureInterface
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

        foreach (Goals::ALL_PAYMENT_GOALS as $index => $name){

            $randomDayOffset = $this->faker->numberBetween(3, 100);

            $randomDatetime  = $this->faker->dateTimeBetween('-14 day', '+1 month');
            $clonedDatetime  = clone $randomDatetime;
            $offsetDatetime  = $clonedDatetime->modify("+{$randomDayOffset} day");

            $startDate       = $randomDatetime->format('Y-m-d');
            $endDate         = $offsetDatetime->format('Y-m-d');

            $display         = $this->faker->boolean;
            $moneyCollected  = $this->faker->numberBetween(500, 2500);
            $moneyGoal       = $this->faker->numberBetween(500, 2500);

            $myGoalPayment  = new MyGoalsPayments();
            $myGoalPayment->setName($name);
            $myGoalPayment->setDisplayOnDashboard($display);
            $myGoalPayment->setCollectionStartDate($startDate);
            $myGoalPayment->setDeadline($endDate);
            $myGoalPayment->setMoneyCollected($moneyCollected);
            $myGoalPayment->setMoneyGoal($moneyGoal);

            $manager->persist($myGoalPayment);

        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 12;
    }
}
