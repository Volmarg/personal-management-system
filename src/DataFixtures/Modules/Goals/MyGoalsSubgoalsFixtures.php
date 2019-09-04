<?php

namespace App\DataFixtures\Modules\Goals;

use App\DataFixtures\Providers\Modules\Goals;
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

    /**
     * @var Goals
     */
    private $provider_goals;

    public function __construct() {
        $this->faker          = Factory::create('en');
        $this->provider_goals = new Goals();

    }

    public function load(ObjectManager $manager)
    {


        foreach($this->provider_goals::ALL_GOALS as $name => $subgoals) {

            $goals = $manager->getRepository(MyGoals::class)->findBy(['name' => $name]);
            $goal  = reset($goals);

            foreach($subgoals as $subgoal_name => $completing_status){
                $my_goal_subgoal = new MyGoalsSubgoals();
                $my_goal_subgoal->setMyGoal($goal);
                $my_goal_subgoal->setName($subgoal_name);
                $my_goal_subgoal->setCompleted($completing_status);

                $manager->persist($my_goal_subgoal);
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
        return 11;
    }
}
