<?php

namespace App\Repository\Modules\Job;

use App\Entity\Modules\Job\MyJobHolidaysPool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyJobHolidaysPool|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyJobHolidaysPool|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyJobHolidaysPool[]    findAll()
 * @method MyJobHolidaysPool[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyJobHolidaysPoolRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyJobHolidaysPool::class);
    }

    public function getHolidaysSummaryGroupedByYears() {

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT 
                mjhp.year                                       AS year,
                mjhp.days_left                                  AS daysForYear,
                SUM(mjh_days_spent.days_spent)                  AS daysSpent,
                CASE
                  WHEN SUM(mjh_days_spent.days_spent) IS NULL THEN mjhp.days_left 
                ELSE
                  mjhp.days_left - SUM(mjh_days_spent.days_spent)
                END                                             AS daysLeftForYear
            
            FROM my_job_holiday_pool mjhp
            
            -- now I join my_job_holiday to get amount of days spent
            LEFT JOIN my_job_holiday mjh_days_spent
                ON mjh_days_spent.year = mjhp.year
                AND mjh_days_spent.deleted = 0
            
            WHERE mjhp.deleted = 0
            -- because as result I want summary for each year
            GROUP BY year
        ";

        $statement = $connection->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();

        return $results;
    }

    public function getAvailableDaysTotally(){

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT 
                -- SUM(mjhp.days_left)                                     AS daysAvailableForAllYears,
                -- daysSpent.daysSpentForAllYears                          AS daysSpentForAllYears,
                SUM(mjhp.days_left) - daysSpent.daysSpentForAllYears    AS daysAvailableTotally
            
            FROM my_job_holiday_pool mjhp
            
            -- now I join my_job_holiday to get amount of days spent
            JOIN 
            (
              SELECT SUM(mjh_days_spent.days_spent) AS daysSpentForAllYears
              FROM my_job_holiday mjh_days_spent
              WHERE deleted = 0
            ) AS daysSpent
            
            WHERE deleted = 0
        ";

        $statement = $connection->prepare($sql);
        $statement->execute();
        $result = $statement->fetchColumn();

        return $result;
    }

    public function getAllPoolsYears(){

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT DISTINCT year
            FROM my_job_holiday_pool
            WHERE deleted = 0 ;
        ";

        $statement = $connection->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();

        return ( !empty($results) ? array_column($results, 'year') : [] );
    }

}
