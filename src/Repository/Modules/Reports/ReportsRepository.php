<?php

namespace App\Repository\Modules\Reports;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;

class ReportsRepository{

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * This function sums up all payments from :
     *  - monthly payments
     *  - bills
     * @return array
     * @throws DBALException
     */
    public function buildPaymentsSummariesForMonthsAndYears() {
        $connection = $this->em->getConnection();

        $sql = "
            SELECT
                DATE_FORMAT(mpm.date,'%Y-%m') AS yearAndMonth,
                ROUND(
                    SUM(
                        mpm.money
                    ) +
                IF(
                    DATE_FORMAT(mpm.date,'%Y-%m') = yearAndMonth,
                    CASE
                        WHEN payment_bills.money IS NULL THEN 0
                    ELSE
                        payment_bills.money
                    END,
                    0
                ),
                2) AS money
            
            FROM my_payment_monthly mpm
            
            LEFT JOIN (
                SELECT
                DATE_FORMAT(mpbi.date,'%Y-%m') AS yearAndMonth,
                SUM(mpbi.amount) AS money
            
                FROM my_payment_bill_item mpbi
            
                GROUP BY (
                    DATE_FORMAT(mpbi.date,'%Y-%m')
                )
            ) AS payment_bills
            ON DATE_FORMAT(mpm.date,'%Y-%m') = payment_bills.yearAndMonth
            
            GROUP BY (
                DATE_FORMAT(mpm.date,'%Y-%m')
            )
        ";

        $stmt = $connection->executeQuery($sql);
        $results = $stmt->fetchAll();

        return $results;
    }


}