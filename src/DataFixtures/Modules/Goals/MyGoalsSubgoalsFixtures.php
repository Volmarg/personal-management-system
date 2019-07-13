<?php

namespace App\DataFixtures\Modules\Goals;

use App\Entity\Modules\Goals\MyGoals;
use App\Entity\Modules\Goals\MyGoalsSubgoals;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyGoalsSubgoalsFixtures extends Fixture implements OrderedFixtureInterface
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

        $my_goals               = $manager->getRepository(MyGoals::class)->findAll();

        for($x = 0; $x <= 26; $x++) {

            $index              = array_rand($my_goals);
            $my_goal            = $my_goals[$index];
            $completed          = $this->faker->boolean;
            $name               = $this->faker->word;

            $my_goal_subgoal    = new MyGoalsSubgoals();
            $my_goal_subgoal->setMyGoal($my_goal);
            $my_goal_subgoal->setName($name);
            $my_goal_subgoal->setCompleted($completed);

            $manager->persist($my_goal_subgoal);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 11;
    }
}
