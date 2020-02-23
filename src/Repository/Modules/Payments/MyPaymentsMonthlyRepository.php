<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsMonthly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyPaymentsMonthly|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsMonthly|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsMonthly[]    findAll()
 * @method MyPaymentsMonthly[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsMonthlyRepository extends ServiceEntityRepository {

    const KEY_COLUMN_NAME_DATE = "date";

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyPaymentsMonthly::class);
    }

    public function fetchAllDateGroups() {
        $em = $this->getEntityManager();

        $qb = $em->createQuery("
            SELECT mpm.date, YEAR(mpm.date) AS HIDDEN group_date_year, MONTH(mpm.date) AS HIDDEN group_date_month
            FROM App\Entity\Modules\Payments\MyPaymentsMonthly mpm
            WHERE mpm.deleted = 0
            GROUP BY group_date_year, group_date_month
        ");

        return $qb->execute();
    }

    public function getPaymentsByTypes() {
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
          GROUP BY mpm.type_id, group_date_year, group_date_month
          ORDER BY LENGTH(mps.value) ASC,
            type_id DESC
        ";

        $statement = $connection->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();

        return $results;
    }

    /**
     * @param string $hash
     * @return MyPaymentsMonthly[]
     */
    public function findByDateAndDescriptionHash(string $hash){

        $queryBuilder = $this->createQueryBuilder('pm');

        $queryBuilder
            ->where("SHA1(CONCAT(pm.date, pm.description)) = :hash")
            ->setParameter('hash', $hash);

        $query = $queryBuilder->getQuery();

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

}
