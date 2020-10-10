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
        foreach(Todo::ALL_TODO_GOALS as $index => $todo_with_elements) {

            foreach($todo_with_elements as $name => $elements){

                $todos = $manager->getRepository(MyTodo::class)->findBy(['name' => $name]);
                $todo  = reset($todos);

                foreach($elements as $element_name => $completing_status){

                    $my_todo_element = new MyTodoElement();
                    $my_todo_element->setMyTodo($todo);
                    $my_todo_element->setName($element_name);
                    $my_todo_element->setCompleted($completing_status);

                    $manager->persist($my_todo_element);
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
        foreach(Todo::ALL_TODO as $index => $todo_with_elements) {
            foreach($todo_with_elements as $name => $elements){

                $todos = $manager->getRepository(MyTodo::class)->findBy(['name' => $name]);
                $todo  = reset($todos);

                foreach($elements as $element_name => $completing_status){

                    $my_todo_element = new MyTodoElement();
                    $my_todo_element->setName($element_name);
                    $my_todo_element->setMyTodo($todo);
                    $my_todo_element->setCompleted($completing_status);

                    $manager->persist($my_todo_element);
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
        foreach(Todo::ALL_TODO_ISSUE as $index => $todo_with_elements) {

            foreach($todo_with_elements as $name => $elements){

                $todos = $manager->getRepository(MyTodo::class)->findBy(['name' => $name]);
                $todo  = reset($todos);

                foreach($elements as $element_name => $completing_status){

                    $my_todo_element = new MyTodoElement();
                    $my_todo_element->setMyTodo($todo);
                    $my_todo_element->setName($element_name);
                    $my_todo_element->setCompleted($completing_status);

                    $manager->persist($my_todo_element);
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