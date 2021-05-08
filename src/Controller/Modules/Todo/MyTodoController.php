<?php

namespace App\Controller\Modules\Todo;

use App\Controller\Core\Application;
use App\Controller\Modules\Issues\MyIssuesController;
use App\Controller\Modules\ModulesController;
use App\Controller\System\LockedResourceController;
use App\Controller\System\ModuleController;
use App\DTO\EntityDataDto;
use App\Entity\Interfaces\Relational\RelatesToMyTodoInterface;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\System\LockedResource;
use App\Services\Core\Logger;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyTodoController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var ModuleController $moduleController
     */
    private ModuleController $moduleController;

    /**
     * @var MyIssuesController $issuesController
     */
    private MyIssuesController $issuesController;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private LockedResourceController $lockedResourceController;

    public function __construct(Application $app, ModuleController $moduleController, MyIssuesController $issuesController, LockedResourceController $lockedResourceController)
    {
        $this->app                      = $app;
        $this->issuesController         = $issuesController;
        $this->moduleController         = $moduleController;
        $this->lockedResourceController = $lockedResourceController;
    }

    /**
     * Will return the
     * @param string $moduleName
     * @return MyTodo[]
     */
    public function getTodoForModule(string $moduleName): array
    {
        $entities = $this->app->repositories->myTodoRepository->getEntitiesForModuleName($moduleName);
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
     *
     * @param bool $deleted
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function getAllGroupedByModuleName(bool $deleted = false): array
    {
        $groupedEntities = [];
        $allEntities     = $this->getAll($deleted);

        foreach($allEntities as $entity){

            $moduleName = ( is_null($entity->getModule()) ? null : $entity->getModule()->getName()) ;
            if(
                    !is_null($moduleName)
                &&  !$this->lockedResourceController->isAllowedToSeeResource("", LockedResource::TYPE_ENTITY, $moduleName, false)
            ){
                continue;
            }

            $groupedEntities[$moduleName][] = $entity;
        }

        return $groupedEntities;
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
     * @param int $todoId
     * @return bool
     * @throws DBALException
     */
    public function areAllElementsDone(int $todoId): bool
    {
        $areElementsDone = $this->app->repositories->myTodoRepository->areAllElementsDone($todoId);
        return $areElementsDone;
    }

    /**
     * Will set relation with `todo` with given entity in module
     *
     * @param MyTodo $todo
     */
    public function setRelationForTodo(MyTodo $todo): void
    {
        $entityId = $todo->getRelatedEntityId();
        $module   = $todo->getModule();

        if( empty($module) ){
            $this->app->logger->info("Not setting relation to myTodo as no related module was selected");
            return;
        }

        $moduleName      = $module->getName();
        $entityNamespace = ModulesController::getEntityNamespaceForModuleName($moduleName);

        if( empty($entityId) ){
            $this->app->logger->info("Not setting relation to myTodo as no entity was give to relate with");
            return;
        }

        if( is_null($entityNamespace) ){
            $this->app->logger->warning("Cannot set relation to MyTodo as no entity was found for module name", [
                Logger::KEY_MODULE_NAME => $moduleName,
                Logger::KEY_ID          => $entityId,
            ]);
            return;
        }

        $entity = $this->getDoctrine()->getManager()->find($entityNamespace, $entityId);

        if( !$entity instanceof RelatesToMyTodoInterface ){
            $this->app->logger->warning("Cannot set relation to MyTodo as this entity does not implements relation interface", [
                Logger::KEY_MODULE_NAME => $moduleName,
                Logger::KEY_ID          => $entityId,
            ]);
            return;
        }

        $entity->setTodo($todo);

        if( is_null($entity) ){
            $this->app->logger->warning("Cannot set relation to MyTodo as no entity namespace mapping is defined for given module name", [
                Logger::KEY_MODULE_NAME => $moduleName,
                Logger::KEY_ID          => $entityId,
            ]);
            return;
        }
    }

    /**
     * Will return one module entity for given name or null if no matching module with this name was found
     *
     * @param string $moduleName
     * @param int $entityId
     * @return MyTodo|null
     * @throws NonUniqueResultException
     */
    public function getTodoByModuleNameAndEntityId(string $moduleName, int $entityId): ?MyTodo
    {
        return $this->app->repositories->myTodoRepository->getTodoByModuleNameAndEntityId($moduleName, $entityId);
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MyTodo|null
     */
    public function findOneById(int $id): ?MyTodo
    {
        return $this->app->repositories->myTodoRepository->findOneById($id);
    }

    /**
     * Will return all entities which can relate with `todo` for given modules
     * @return EntityDataDto[]
     */
    public function getAllRelatableEntitiesDataDtosForModulesNames(): array
    {
        $allModules                       = $this->moduleController->getAllActive();
        $relatableEntitiesForModulesNames = [];

        foreach( $allModules as $module ){
            $moduleName = $module->getName();

            switch( $moduleName ){
                case ModulesController::MODULE_NAME_ISSUES:
                {
                    $allNotDeletedIssues = $this->issuesController->findAllNotDeletedAndNotResolved();

                    foreach( $allNotDeletedIssues as $issueEntity ){
                        $entityDataDto = new EntityDataDto();
                        $entityDataDto->setId($issueEntity->getId());
                        $entityDataDto->setName($issueEntity->getName());

                        if( !empty($issueEntity->getTodo()) ){
                            $entityDataDto->setActive(false);;
                        }

                        $relatableEntitiesForModulesNames[$moduleName][] = $entityDataDto;
                    }
                }
                break;
            }

        }

        return $relatableEntitiesForModulesNames;
    }

}