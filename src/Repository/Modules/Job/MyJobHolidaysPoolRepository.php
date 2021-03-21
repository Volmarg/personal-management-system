<?php

namespace App\Repository\Modules\Job;

use App\Entity\Modules\Job\MyJobHolidaysPool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyJobHolidaysPool|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyJobHolidaysPool|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyJobHolidaysPool[]    findAll()
 * @method MyJobHolidaysPool[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyJobHolidaysPoolRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyJobHolidaysPool::class);
    }

    /**
     * @return mixed[]
     * @throws DBALException
     */
    public function getHolidaysSummaryGroupedByYears() {

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT 
                mjhp.year                                       AS year,
                mjhp.days_in_pool                               AS daysForYear,
                SUM(mjh_days_spent.days_spent)                  AS daysSpent,
                CASE
                  WHEN SUM(mjh_days_spent.days_spent) IS NULL THEN mjhp.days_in_pool 
                ELSE
                  mjhp.days_in_pool - SUM(mjh_days_spent.days_spent)
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

    /**
     * @return int
     * @throws DBALException
     * @throws Exception
     */
    public function getAvailableDaysTotally(): int
    {

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT 
                SUM(mjhp.days_in_pool) - daysSpent.daysSpentForAllYears    AS daysAvailableTotally
            
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
        $result = $statement->fetchOne();

        if( is_null($result) ){
            return 0;
        }

        return $result;
    }

    /**
     * @return array
     * @throws DBALException
     */
    public function getAllPoolsYears(){

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT DISTINCT year
            FROM my_job_holiday_pool
            WHERE deleted = 0
            ORDER BY year DESC
        ";

        $statement = $connection->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();

        return ( !empty($results) ? array_column($results, 'year') : [] );
    }

    /**
     * Returns the number of days left for given calendar year
     * @param string $year
     * @return int
     * @throws DBALException
     */
    public function getDaysInPoolForYear(string $year): int
    {
        $connection = $this->_em->getConnection();

        $sql = "
            SELECT hp.days_in_pool as days_in_pool
            FROM my_job_holiday_pool hp
            
            WHERE 1
            AND hp.year = :year
        ";

        $params = [
          "year" => $year,
        ];

        $stmt   = $connection->executeQuery($sql, $params);
        $result = $stmt->fetch();

        if( empty($result) ){
            return 0;
        }

        $daysInPool = (int) $result[MyJobHolidaysPool::FIELD_DAYS_IN_POOL];

        return $daysInPool;
    }

    /**
     * @param int $id
     * @return MyJobHolidaysPool|null
     * @throws NonUniqueResultException
     */
    public function findOneEntityById(int $id):? MyJobHolidaysPool
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("hp")
            ->from(MyJobHolidaysPool::class, "hp")
            ->where('hp.id = :id')
            ->setParameter("id", $id);

        $query  = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * Returns the number of days left for given calendar year
     * @param string $year
     * @return int
     * @throws DBALException
     */
    public function getDaysInPoolLeftForYear(string $year): int
    {
        $connection = $this->_em->getConnection();

        $sql = "
            SELECT 
            hp.days_in_pool - IF( SUM(h.days_spent)  IS NULL, 0 ,SUM(h.days_spent) ) AS daysLeft
            
            FROM my_job_holiday_pool hp
            
            LEFT JOIN my_job_holiday h
            ON h.year     = hp.year
            AND h.deleted = 0
            
            WHERE 1
            AND hp.deleted = 0
            AND hp.year    = :year
        ";

        $params = [
            "year" => $year,
        ];

        $stmt   = $connection->executeQuery($sql, $params);
        $result = $stmt->fetch();

        if( empty($result) ){
            return 0;
        }

        $daysLeft = (int) $result['daysLeft'];

        return $daysLeft;
    }

    /**
     * Will return all not deleted entities
     *
     * @return MyJobHolidaysPool[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->findBy(['deleted' => 0]);
    }
}
