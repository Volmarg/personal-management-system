<?php

namespace App\Repository\Modules\Goals;

use App\Entity\Modules\Goals\MyGoals;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyGoals|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyGoals|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyGoals[]    findAll()
 * @method MyGoals[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyGoalsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyGoals::class);
    }

    public function areAllSubgoalsDone($goal_id){

        $connection = $this->getEntityManager()->getConnection();

        $sql ="
            SELECT COUNT(my_goal_subgoal.id) > 0
            FROM my_goal_subgoal
            WHERE my_goal_id = :goal_id
            AND completed = 1
        ";

        $binded_values  = [
            'goal_id'   => $goal_id
        ];

        $statement      = $connection->prepare($sql);
        $statement->execute($binded_values);
        $result         = $statement->fetchColumn();

        return $result;

    }

    public function changeGoalCompetition($goal_id, $completiton_status){

        $connection = $this->getEntityManager()->getConnection();

        $sql ="
            UPDATE my_goal 
                SET completed = :completed
            WHERE id = :id
        ";

        $binded_values = [
            'completed' => $completiton_status,
            'id'        => $goal_id
        ];

        $statement      = $connection->prepare($sql);
        $statement->execute($binded_values);
        $update_result  = $statement->fetchAll();

        return $update_result;

    }

}
