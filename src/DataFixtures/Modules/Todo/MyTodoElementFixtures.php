<?php
namespace App\DataFixtures\Modules\Todo;

use App\DataFixtures\Providers\Modules\Todo;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\Modules\Todo\MyTodoElement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MyTodoElementFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->addTodoGoalElements($manager);
        $this->addTodoElements($manager);
        $this->addTodoIssueElements($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addTodoGoalElements(ObjectManager $manager): void
    {
        foreach(Todo::ALL_TODO_GOALS as $index => $todoWithElements) {

            foreach($todoWithElements as $name => $elements){

                $todos = $manager->getRepository(MyTodo::class)->findBy(['name' => $name]);
                $todo  = reset($todos);

                foreach($elements as $elementName => $completingStatus){

                    $myTodoElement = new MyTodoElement();
                    $myTodoElement->setMyTodo($todo);
                    $myTodoElement->setName($elementName);
                    $myTodoElement->setCompleted($completingStatus);

                    $manager->persist($myTodoElement);
                }
            }
        }

        $manager->flush();;
    }

    /**
     * @param ObjectManager $manager
     */
    private function addTodoElements(ObjectManager $manager): void
    {
        foreach(Todo::ALL_TODO as $index => $todoWithElements) {
            foreach($todoWithElements as $name => $elements){

                $todos = $manager->getRepository(MyTodo::class)->findBy(['name' => $name]);
                $todo  = reset($todos);

                foreach($elements as $elementName => $completingStatus){

                    $myTodoElement = new MyTodoElement();
                    $myTodoElement->setName($elementName);
                    $myTodoElement->setMyTodo($todo);
                    $myTodoElement->setCompleted($completingStatus);

                    $manager->persist($myTodoElement);
                }
            }
        }

        $manager->flush();;
    }

    /**
     * @param ObjectManager $manager
     */
    private function addTodoIssueElements(ObjectManager $manager): void
    {
        foreach(Todo::ALL_TODO_ISSUE as $index => $todoWithElements) {

            foreach($todoWithElements as $name => $elements){

                $todos = $manager->getRepository(MyTodo::class)->findBy(['name' => $name]);
                $todo  = reset($todos);

                foreach($elements as $elementName => $completingStatus){

                    $myTodoElement = new MyTodoElement();
                    $myTodoElement->setMyTodo($todo);
                    $myTodoElement->setName($elementName);
                    $myTodoElement->setCompleted($completingStatus);

                    $manager->persist($myTodoElement);
                }
            }
        }

        $manager->flush();;
    }

    public function getOrder()
    {
        return 21;
    }
}