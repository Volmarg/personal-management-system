<?php

namespace App\Repository\Modules\Todo;

use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\System\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyTodo|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyTodo|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyTodo[]    findAll()
 * @method MyTodo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyTodoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyTodo::class);
    }

    /**
     * Will return the todoEntities for given module name
     *
     * @param string $moduleName
     * @param bool $forDashboard
     * @return MyTodo[]
     */
    public function getEntitiesForModuleName(string $moduleName, bool $forDashboard = false): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select("td")
            ->from(MyTodo::class, "td")
            ->join(Module::class, "m", "WITH", "m.id = td.module")
            ->where("m.name = :module_name")
            ->andWhere("td.deleted = 0")
            ->setParameter("module_name", $moduleName);

        if($forDashboard)
        {
            $queryBuilder->andWhere("td.displayOnDashboard = 1");
        }

        $query   = $queryBuilder->getQuery();
        $results = $query->execute();

        return $results;
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
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select("td")
            ->from(MyTodo::class, "td");

        if( !$deleted )
        {
            $queryBuilder->where("td.deleted = 0");
        }

        $query  = $queryBuilder->getQuery();
        $result = $query->execute();

        return $result;
    }

    /**
     * @param MyTodo $myTodo
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(MyTodo $myTodo)
    {
        $this->_em->persist($myTodo);
        $this->_em->flush();
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
        $connection = $this->getEntityManager()->getConnection();

        $sql ="
            SELECT COUNT(mte.id) > 0
            
            FROM my_todo mt
            
            JOIN my_todo_element mte
            ON mt.id = mte.my_todo_id 
            
            WHERE mt.id = :todo_id
            AND mte.completed = 0
        ";

        $bindedValues  = [
            'todo_id' => $todoId
        ];

        $statement = $connection->prepare($sql);
        $statement->execute($bindedValues);
        $result    = (int) $statement->fetchColumn();

        return (0 === $result);
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
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select("td")
            ->from(MyTodo::class, "td")
            ->join(Module::class, "m")
            ->where("m.name = :moduleName")
            ->setParameter("moduleName", $moduleName);

        switch($moduleName)
        {
            case ModulesController::MODULE_NAME_ISSUES:
                {
                    $queryBuilder->join(MyIssue::class, 'iss', "WITH", "iss.todo = td.id")
                        ->andWhere("iss.id = :entityId")
                        ->setParameter("entityId", $entityId);
                }
            break;

            case ModulesController::MODULE_NAME_GOALS:
                {
                    // todo
                }
            break;

            default:
                throw new \Exception("This module name is not supported: " . $moduleName);
        }

        $query  = $queryBuilder->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MyTodo|null
     */
    public function findOneById(int $id): ?MyTodo
    {
        return $this->find($id);
    }

}
