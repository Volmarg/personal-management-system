<?php
namespace App\DataFixtures\Modules\Todo;

use App\Controller\Modules\ModulesController;
use App\DataFixtures\Providers\Modules\Todo;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\System\Module;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MyTodoFixtures extends Fixture implements OrderedFixtureInterface
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
     */
    public function load(ObjectManager $manager): void
    {
        $this->addTodoGoals($manager);
        $this->addTodo($manager);
        $this->addIssueTodo($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addTodoGoals(ObjectManager $manager): void
    {
        $goalsModule = $manager->getRepository(Module::class)->findOneBy([Module::FIELD_NAME => ModulesController::MODULE_NAME_GOALS]);

        foreach(Todo::ALL_TODO_GOALS as $index => $todoWithElements) {

            foreach($todoWithElements as $todoName => $elements) {

                $displayOnDashboard = $this->faker->boolean;

                $myTodo = new MyTodo();
                $myTodo->setName($todoName);
                $myTodo->setModule($goalsModule);
                $myTodo->setDisplayOnDashboard($displayOnDashboard);

                $manager->persist($myTodo);
            }
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function addTodo(ObjectManager $manager): void
    {
        foreach(Todo::ALL_TODO as $index=> $todoWithElements) {

            foreach($todoWithElements as $todoName => $elements) {

                $displayOnDashboard = $this->faker->boolean;

                $myTodo = new MyTodo();
                $myTodo->setName($todoName);
                $myTodo->setDisplayOnDashboard($displayOnDashboard);

                $manager->persist($myTodo);
            }
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function addIssueTodo(ObjectManager $manager): void
    {
        $issueModule = $manager->getRepository(Module::class)->findOneBy([Module::FIELD_NAME => ModulesController::MODULE_NAME_ISSUES]);

        foreach(Todo::ALL_TODO_ISSUE as $issueId => $todoWithElements) {

            foreach($todoWithElements as $todoName => $elements) {

                $issue = $manager->getRepository(MyIssue::class)->findOneBy([MyIssue::FIELD_NAME_ID => $issueId]);

                $displayOnDashboard = $this->faker->boolean;

                $myTodo = new MyTodo();
                $myTodo->setName($todoName);
                $myTodo->setMyIssue($issue);
                $myTodo->setModule($issueModule);
                $myTodo->setDisplayOnDashboard($displayOnDashboard);

                $manager->persist($myTodo);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 20;
    }
}