<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsMonthly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPaymentsMonthly|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsMonthly|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsMonthly[]    findAll()
 * @method MyPaymentsMonthly[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsMonthlyRepository extends ServiceEntityRepository {

    const KEY_COLUMN_NAME_DATE = "date";

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyPaymentsMonthly::class);
    }

    public function fetchAllDateGroupsForYear(string $year) {
        $em = $this->getEntityManager();

        $qb = $em->createQuery("
            SELECT mpm.date, YEAR(mpm.date) AS HIDDEN group_date_year, MONTH(mpm.date) AS HIDDEN group_date_month
            FROM App\Entity\Modules\Payments\MyPaymentsMonthly mpm
            WHERE mpm.deleted = 0
            AND DATE_FORMAT(mpm.date, '%Y') = :year
            GROUP BY group_date_year, group_date_month
        ");

        $params = [
            'year' => $year,
        ];

        return $qb->execute($params);
    }

    public function getPaymentsByTypesForYear(string $year) {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "
          SELECT ROUND(sum(mpm.money),2) AS money, 
            mpm.date,
            mps.value AS type,
            mps.id AS type_id,
            YEAR(mpm.date) AS group_date_year, 
            MONTH(mpm.date) AS group_date_month 
          FROM my_payment_monthly mpm
          JOIN my_payment_setting mps
            ON mpm.type_id = mps.id
            AND mpm.deleted = 0
          WHERE mpm.deleted = 0 
          AND DATE_FORMAT(mpm.date, '%Y') = :year
          GROUP BY mpm.type_id, group_date_year, group_date_month
          ORDER BY LENGTH(mps.value) ASC,
            type_id DESC
        ";

        $parameters = [
            "year" => $year,
        ];

        $statement = $connection->prepare($sql);
        $statement->execute($parameters);
        $results = $statement->fetchAll();

        return $results;
    }

    /**
     * @param string $hash
     * @param string $dayOfMonth
     * @return MyPaymentsMonthly[]
     */
    public function findByDateAndDescriptionHash(string $hash, string $dayOfMonth): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder
            ->select("pm")
            ->from(MyPaymentsMonthly::class, "pm")
            ->where("MD5(CONCAT(DATE_FORMAT(pm.date, '%Y-%m-'), :dayOfMonth, pm.description)) = :hash")
            ->setParameter('hash', $hash)
            ->setParameter("dayOfMonth", $dayOfMonth);

        $query   = $queryBuilder->getQuery();
        $results = $query->getResult();

        return $results;
    }

    /**
     * This function will return dates in format Y-m so that incomes will be added for each month
     * @throws DBALException
     */
    public function getUniqueDatesFromPayments(): array
    {
        $connection = $this->_em->getConnection();

        $sql = "
            SELECT
            DATE_FORMAT(mpm.date, '%Y-%m')AS date
            
            FROM my_payment_monthly mpm
            
            
            WHERE 1
            AND mpm.deleted = 0
            
            GROUP BY DATE_FORMAT(mpm.date, '%Y-%m')
        ";

        $stmt    = $connection->executeQuery($sql);
        $results = $stmt->fetchAll(0);
        $dates   = array_column($results,self::KEY_COLUMN_NAME_DATE);

        return $dates;
    }

    /**
     * Will return all not deleted entities
     *
     * @param string $year
     * @return array
     */
    public function getAllNotDeletedForYear(string $year): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select("mpm")
            ->from(MyPaymentsMonthly::class, "mpm")
            ->where("mpm.deleted = 0")
            ->andWhere("DATE_FORMAT(mpm.date, '%Y') = :year")
            ->setParameter("year", $year);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsMonthly|null
     */
    public function findOneById(int $id): ?MyPaymentsMonthly
    {
        return $this->find($id);
    }

}
