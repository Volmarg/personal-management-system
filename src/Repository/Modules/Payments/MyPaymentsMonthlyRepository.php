<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsMonthly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyPaymentsMonthly|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsMonthly|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsMonthly[]    findAll()
 * @method MyPaymentsMonthly[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsMonthlyRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyPaymentsMonthly::class);
    }

    public function fetchAllDateGroups() {
        $em = $this->getEntityManager();

        $qb = $em->createQuery("
        SELECT mpm.date, YEAR(STR_TO_DATE(mpm.date,'%d-%m-%Y')) AS HIDDEN group_date_year, MONTH(STR_TO_DATE(mpm.date,'%d-%m-%Y')) AS HIDDEN group_date_month
        FROM App\Entity\Modules\Payments\MyPaymentsMonthly mpm
        WHERE mpm.deleted = 0
        GROUP BY group_date_year, group_date_month
        ");

        return $qb->execute();
    }

    public function getPaymentsByTypes() {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "
          SELECT floor(sum(mpm.money)) AS money, 
            mpm.date,
            mps.value AS type,
            mps.id AS type_id,
            YEAR(STR_TO_DATE(mpm.date, '%d-%m-%Y')) AS group_date_year, 
            MONTH(STR_TO_DATE(mpm.date, '%d-%m-%Y')) AS group_date_month 
          FROM my_payments_monthly mpm
          JOIN my_payments_settings mps
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

}
