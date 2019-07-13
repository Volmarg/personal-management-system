<?php

namespace App\DataFixtures\Modules\Goals;

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

    public function __construct() {
        $this->faker = Factory::create('en');

    }

    public function load(ObjectManager $manager)
    {

        for($x = 0; $x <= 10; $x++) {

            $random_day_offset  = $this->faker->numberBetween(3, 100);

            $random_datetime    = $this->faker->dateTimeBetween('-14 day', '+1 month');
            $cloned_datetime    = clone $random_datetime;
            $offset_datetime    = $cloned_datetime->modify("+{$random_day_offset} day");

            $start_date         = $random_datetime->format('Y-m-d');
            $end_date           = $offset_datetime->format('Y-m-d');

            $name               = $this->faker->word;
            $display            = $this->faker->boolean;
            $money_collected    = $this->faker->numberBetween(10, 5000);
            $money_goal         = $this->faker->numberBetween(10, 5000);

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
