<?php

namespace App\DataFixtures\Modules\Goals;

use App\DataFixtures\Providers\Modules\Goals;
use App\Entity\Modules\Goals\MyTodo;
use App\Entity\Modules\Goals\MyTodoElement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MyGoalsSubgoalsFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        foreach(Goals::ALL_GOALS as $index => $goal_with_subgoals) {

            foreach($goal_with_subgoals as $goal_name => $subgoals){

                $goals = $manager->getRepository(MyTodo::class)->findBy(['name' => $goal_name]);
                $goal  = reset($goals);

                foreach($subgoals as $subgoal_name => $completing_status){

                    $my_goal_subgoal = new MyTodoElement();
                    $my_goal_subgoal->setMyGoal($goal);
                    $my_goal_subgoal->setName($subgoal_name);
                    $my_goal_subgoal->setCompleted($completing_status);

                    $manager->persist($my_goal_subgoal);
                }

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
