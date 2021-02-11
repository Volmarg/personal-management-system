<?php

namespace App\Repository\Modules\Job;

use App\Entity\Modules\Job\MyJobAfterhours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyJobAfterhours|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyJobAfterhours|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyJobAfterhours[]    findAll()
 * @method MyJobAfterhours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyJobAfterhoursRepository extends ServiceEntityRepository {

    const GOAL_FIELD         = 'goal';
    const TIME_SUMMARY_FIELD = 'timeSummary';
    const DAYS_SUMMARY_FIELD = 'daysSummary';

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyJobAfterhours::class);
    }

    public function getGoalsWithTime() {

        $sql = "
            SELECT DISTINCT mja.goal,
            CASE
               WHEN SUM(mja2.minutes) IS NULL THEN 0
               ELSE SUM(mja2.minutes)
            END AS timeMade,
            CASE
               WHEN SUM(mja3.minutes) IS NULL THEN 0
               ELSE SUM(mja3.minutes)
            END AS timeSpent,
            CASE
                WHEN SUM(mja2.minutes) IS NOT NULL AND SUM(mja3.minutes) IS NOT NULL THEN 
                  SEC_TO_TIME((SUM(mja2.minutes) - SUM(mja3.minutes)) * 60)
                WHEN SUM(mja2.minutes) IS NOT NULL AND SUM(mja3.minutes) IS NULL THEN 
                  SEC_TO_TIME((SUM(mja2.minutes) - 0) * 60)
               ELSE 0
            END AS timeSummary,
            CASE
                WHEN SUM(mja2.minutes) IS NOT NULL AND SUM(mja3.minutes) IS NOT NULL THEN 
                  ROUND((SUM(mja2.minutes) - SUM(mja3.minutes))/60/8, 2)
                WHEN SUM(mja2.minutes) IS NOT NULL AND SUM(mja3.minutes) IS NULL THEN 
                  ROUND((SUM(mja2.minutes) - 0)/60/8, 2)
               ELSE 0
            END AS daysSummary
            
            FROM my_job_afterhour AS mja
            LEFT JOIN my_job_afterhour AS mja2
              ON mja.id = mja2.id
              AND mja2.type = :type_made
              AND mja2.deleted = 0
            LEFT JOIN my_job_afterhour AS mja3
              ON mja.id = mja3.id
              AND  mja3.type = :type_spent
              AND mja3.deleted = 0
            WHERE mja.deleted = 0
            GROUP BY mja.goal
            HAVING (timeMade - timeSpent > 0)
        ";

        $bindedValues = [
            'type_made'  => MyJobAfterhours::TYPE_MADE,
            'type_spent' => MyJobAfterhours::TYPE_SPENT,
        ];

        $connection = $this->getEntityManager()->getConnection();
        $statement  = $connection->prepare($sql);

        $statement->execute($bindedValues);
        $results = $statement->fetchAll();

        return (!empty($results) ? $results : []);
    }

    public function getAllGoalsNames(){

        $sql = "
            SELECT DISTINCT goal
            FROM my_job_afterhour
            WHERE 1
                AND goal IS NOT NULL;
        ";

        $connection = $this->getEntityManager()->getConnection();
        $statement  = $connection->prepare($sql);

        $statement->execute();
        $results = $statement->fetchAll();

        return (!empty($results) ? array_column($results,'goal') : []);
    }

    /**
     * Will search for not deleted afterhours by types
     *
     * @param string[] $types
     * @return MyJobAfterhours[]
     */
    public function findAllNotDeletedByType(array $types): array
    {
        $entities = $this->findBy([
            MyJobAfterhours::FIELD_NAME_DELETED => 0,
            MyJobAfterhours::FIELD_NAME_TYPE    => $types
        ]);

        return $entities;
    }

    /**
     * Will return one entity for given id, if such does not exist then null will be returned
     *
     * @param int $id
     * @return MyJobAfterhours|null
     */
    public function findOneById(int $id): ?MyJobAfterhours
    {
        return $this->find($id);
    }

}
