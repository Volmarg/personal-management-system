<?php

namespace App\DataFixtures\Modules\Goals;

use App\DataFixtures\Providers\Modules\Goals;
use App\Entity\Modules\Goals\MyGoalsPayments;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyGoalsPaymentsFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory $faker
     */
    private $faker;

    /**
     * @var Goals
     */
    private $provider_goals;

    public function __construct() {
        $this->faker          = Factory::create('en');
        $this->provider_goals = new Goals();

    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        foreach ($this->provider_goals::ALL_PAYMENT_GOALS as $index => $name){

            $random_day_offset  = $this->faker->numberBetween(3, 100);

            $random_datetime    = $this->faker->dateTimeBetween('-14 day', '+1 month');
            $cloned_datetime    = clone $random_datetime;
            $offset_datetime    = $cloned_datetime->modify("+{$random_day_offset} day");

            $start_date         = $random_datetime->format('Y-m-d');
            $end_date           = $offset_datetime->format('Y-m-d');

            $display            = $this->faker->boolean;
            $money_collected    = $this->faker->numberBetween(500, 2500);
            $money_goal         = $this->faker->numberBetween(500, 2500);

            $my_goal_payment  = new MyGoalsPayments();
            $my_goal_payment->setName($name);
            $my_goal_payment->setDisplayOnDashboard($display);
            $my_goal_payment->setCollectionStartDate($start_date);
            $my_goal_payment->setDeadline($end_date);
            $my_goal_payment->setMoneyCollected($money_collected);
            $my_goal_payment->setMoneyGoal($money_goal);

            $manager->persist($my_goal_payment);

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
