<?php

namespace App\Controller\Modules\Todo;

use App\Controller\Core\Application;
use App\Entity\Modules\Todo\MyTodoElement;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyTodoElementController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @param string $module_name
     * @return MyTodoElement[]
     */
    public function getTodoElementsForModule(string $module_name): array
    {
        $todos_elements = [];
        $todos          = $this->app->repositories->myTodoRepository->getEntitiesForModuleName($module_name);

        foreach($todos as $todo){
            $elements       = $todo->getMyTodoElement();
            $todos_elements = array_merge($todos_elements, $elements);
        }

        return $todos_elements;
    }

    /**
     * @param MyTodoElement $todo_element
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(MyTodoElement $todo_element): void
    {
        $this->app->repositories->myTodoElementRepository->save($todo_element);
    }
}