<?php

namespace App\Repository\Modules\Job;

use App\Entity\Modules\Job\MyJobHolidaysPool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
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
        return $this->findBy(['deleted' => 0], ["year" => "DESC"]);
    }
}
