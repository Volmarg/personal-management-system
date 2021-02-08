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
     * @param string $moduleName
     * @return MyTodoElement[]
     */
    public function getTodoElementsForModule(string $moduleName): array
    {
        $todosElements = [];
        $todos          = $this->app->repositories->myTodoRepository->getEntitiesForModuleName($moduleName);

        foreach($todos as $todo){
            $elements      = $todo->getMyTodoElement();
            $todosElements = array_merge($todosElements, $elements);
        }

        return $todosElements;
    }

    /**
     * @param MyTodoElement $todoElement
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(MyTodoElement $todoElement): void
    {
        $this->app->repositories->myTodoElementRepository->save($todoElement);
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MyTodoElement|null
     */
    public function findOneById(int $id): ?MyTodoElement
    {
        return $this->app->repositories->myTodoElementRepository->findOneById($id);
    }

}