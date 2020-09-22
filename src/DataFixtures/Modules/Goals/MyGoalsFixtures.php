<?php

namespace App\DataFixtures\Modules\Goals;

use App\DataFixtures\Providers\Modules\Goals;
use App\Entity\Modules\Goals\MyTodo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyGoalsFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory $faker
     */
    private $faker;

    public function __construct() {
        $this->faker = Factory::create('en');
    }

    public function load(ObjectManager $manager) {

        foreach(Goals::ALL_GOALS as $name => $goal_with_subgoals) {

            foreach($goal_with_subgoals as $goal_name => $subgoals) {

                $display_on_dashboard = $this->faker->boolean;

                $my_goal = new MyTodo();
                $my_goal->setName($goal_name);
                $my_goal->setDescription('');
                $my_goal->setDisplayOnDashboard($display_on_dashboard);

                $manager->persist($my_goal);
            }

        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 10;
    }
}
