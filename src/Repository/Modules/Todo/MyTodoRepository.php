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
     * @param string $module_name
     * @param bool $for_dashboard
     * @return MyTodo[]
     */
    public function getEntitiesForModuleName(string $module_name, bool $for_dashboard = false): array
    {
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder->select("td")
            ->from(MyTodo::class, "td")
            ->join(Module::class, "m", "WITH", "m.id = td.module")
            ->where("m.name = :module_name")
            ->andWhere("td.deleted = 0")
            ->setParameter("module_name", $module_name);

        if($for_dashboard)
        {
            $query_builder->andWhere("td.displayOnDashboard = 1");
        }

        $query   = $query_builder->getQuery();
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
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder->select("td")
            ->from(MyTodo::class, "td");

        if( !$deleted )
        {
            $query_builder->where("td.deleted = 0");
        }

        $query  = $query_builder->getQuery();
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
     * @param int $todo_id
     * @return bool
     * @throws DBALException
     */
    public function areAllElementsDone(int $todo_id): bool
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

        $binded_values  = [
            'todo_id' => $todo_id
        ];

        $statement = $connection->prepare($sql);
        $statement->execute($binded_values);
        $result    = (int) $statement->fetchColumn();

        return (0 === $result);
    }

    // todo: need to adjust queries and fetch the via joins to given module for module name

    /**
     * Will return one module entity for given name or null if no matching module with this name was found
     *
     * @param string $module_name
     * @param int $entity_id
     * @return MyTodo|null
     * @throws NonUniqueResultException
     */
    public function getTodoByModuleNameAndEntityId(string $module_name, int $entity_id): ?MyTodo
    {
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder->select("td")
            ->from(MyTodo::class, "td")
            ->join(Module::class, "m")
            ->where("m.name = :moduleName")
            ->setParameter("moduleName", $module_name);

        switch($module_name)
        {
            case ModulesController::MODULE_NAME_ISSUES:
                {
                    $query_builder->join(MyIssue::class, 'iss', "WITH", "iss.todo = td.id")
                        ->andWhere("iss.id = :entityId")
                        ->setParameter("entityId", $entity_id);
                }
            break;

            case ModulesController::MODULE_NAME_GOALS:
                {
                    // todo
                }
            break;

            default:
                throw new \Exception("This module name is not supported: " . $module_name);
        }

        $query  = $query_builder->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

}
