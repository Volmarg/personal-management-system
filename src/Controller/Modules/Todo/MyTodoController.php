<?php

namespace App\Controller\Modules\Todo;

use App\Controller\Core\Application;
use App\Controller\Modules\ModulesController;
use App\Entity\Interfaces\Relational\RelatesToMyTodoInterface;
use App\Entity\Modules\Todo\MyTodo;
use App\Services\Core\Logger;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyTodoController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Will return the
     * @param string $module_name
     * @return MyTodo[]
     */
    public function getTodoForModule(string $module_name): array
    {
        $entities = $this->app->repositories->myTodoRepository->getEntitiesForModuleName($module_name);
        return $entities;
    }

    /**
     * Will fetch all MyTodo entities depending on the:
     * - deleted
     * - completed
     * state
     *
     * @param bool $deleted
     * @return MyTodo[]
     */
    public function getAll(bool $deleted = false): array
    {
        $entities = $this->app->repositories->myTodoRepository->getAll($deleted);
        return $entities;
    }

    /**
     * Will fetch all MyTodo entities grouped by associated module depending on the:
     * - deleted
     * - completed
     * state
     * @param bool $deleted
     * @return array
     */
    public function getAllGroupedByModuleName(bool $deleted = false): array
    {
        $grouped_entities = [];
        $all_entities     = $this->getAll($deleted);

        foreach($all_entities as $entity)
        {
            $module_name                      = ( is_null($entity->getModule()) ? null : $entity->getModule()->getName()) ;
            $grouped_entities[$module_name][] = $entity;
        }

        return $grouped_entities;
    }

    /**
     * Will save entity state in db
     *
     * @param MyTodo $myTodo
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function save(MyTodo $myTodo): void
    {
        $this->app->repositories->myTodoRepository->save($myTodo);
    }

    /**
     * Will check if al elements in single todo are done
     *
     * @param int $todo_id
     * @return bool
     * @throws DBALException
     */
    public function areAllElementsDone(int $todo_id): bool
    {
        $are_elements_done = $this->app->repositories->myTodoRepository->areAllElementsDone($todo_id);
        return $are_elements_done;
    }

    /**
     * Will set relation with `todo` with given entity in module
     *
     * @param MyTodo $todo
     */
    public function setRelationForTodo(MyTodo $todo): void
    {
        $entity_id        = $todo->getRelatedEntityId();
        $module_name      = $todo->getModule()->getName();
        $entity_namespace = ModulesController::getEntityNamespaceForModuleName($module_name);

        if( is_null($entity_namespace) ){
            $this->app->logger->warning("Cannot set relation to MyTodo as no entity was found for module name", [
                Logger::KEY_MODULE_NAME => $module_name,
                Logger::KEY_ID          => $entity_id,
            ]);
            return;
        }

        $entity = $this->getDoctrine()->getManager()->find($entity_namespace, $entity_id);

        if( !$entity instanceof RelatesToMyTodoInterface ){
            $this->app->logger->warning("Cannot set relation to MyTodo as this entity does not implements relation interface", [
                Logger::KEY_MODULE_NAME => $module_name,
                Logger::KEY_ID          => $entity_id,
            ]);
            return;
        }

        $entity->setTodo($todo);

        if( is_null($entity) ){
            $this->app->logger->warning("Cannot set relation to MyTodo as no entity namespace mapping is defined for given module name", [
                Logger::KEY_MODULE_NAME => $module_name,
                Logger::KEY_ID          => $entity_id,
            ]);
            return;
        }
    }

}