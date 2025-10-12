<?php

namespace App\Repository\Modules\Reports;

use App\Entity\Modules\Payments\MyPaymentsOwed;
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
            
            dates.yearAndMonth,
            IF(withoutBills.moneyWithoutBills IS NULL, 0, withoutBills.moneyWithoutBills) AS moneyWithoutBills,
            IF(withBills.money IS NULL, 0, withBills.money) + IF(withoutBills.moneyWithoutBills IS NULL, 0, withoutBills.moneyWithoutBills)  AS money
            
            -- first get all the possible dates
             FROM (
                SELECT
                DISTINCT DATE_FORMAT(mpm.date,'%Y-%m') AS yearAndMonth
                FROM my_payment_monthly mpm
                
                WHERE 1
                AND mpm.deleted = 0
                
                UNION 
                
                SELECT
                DISTINCT DATE_FORMAT(mpbi.date,'%Y-%m') AS yearAndMonth
                FROM my_payment_bill_item mpbi
                
                WHERE 1
                AND mpbi.deleted = 0
            
            ) AS dates
        
            -- get money amount per month from payments
            LEFT JOIN (
                SELECT
                DATE_FORMAT(mpmWithoutBill.date,'%Y-%m') AS yearAndMonth,
                ROUND(
                    SUM(
                        mpmWithoutBill.money
                    ) 
                ) AS moneyWithoutBills
        
                FROM my_payment_monthly mpmWithoutBill
                
                WHERE 1
                AND mpmWithoutBill.deleted = 0
                
                GROUP BY DATE_FORMAT(mpmWithoutBill.date,'%Y-%m')
            
            ) AS withoutBills
            ON withoutBills.yearAndMonth = dates.yearAndMonth
        
            -- get money amount from bills per month
            LEFT JOIN (
        
                SELECT
                payment_bills.yearAndMonth AS yearAndMonth,
                IF(
                    datesAndAmount.yearAndMonth = payment_bills.yearAndMonth,
                    CASE
                        WHEN payment_bills.money IS NULL THEN 0
                    ELSE
                        MAX(payment_bills.money)
                    END,
                    0
                 ) AS money
                
                 FROM (
                    SELECT
                    DISTINCT DATE_FORMAT(mpm.date,'%Y-%m')  AS yearAndMonth,
                    ROUND(SUM(mpm.money))                   AS amount
                    FROM my_payment_monthly mpm
                    
                    WHERE 1
                    AND mpm.deleted = 0
                    
                    GROUP BY DATE_FORMAT(mpm.date,'%Y-%m')
                    
                    UNION 
                    
                    SELECT
                    DISTINCT DATE_FORMAT(mpbi.date,'%Y-%m') AS yearAndMonth,
                    0 AS amount
                    FROM my_payment_bill_item mpbi
                    
                    WHERE 1
                    AND mpbi.deleted = 0
                
                ) AS datesAndAmount
                            
                RIGHT JOIN (
                    SELECT
                    DATE_FORMAT(mpbi.date,'%Y-%m') AS yearAndMonth,
                    SUM(mpbi.amount)               AS money
                
                    FROM my_payment_bill_item mpbi
                
                    WHERE 1
                    AND mpbi.deleted = 0
                
                    GROUP BY DATE_FORMAT(mpbi.date,'%Y-%m')
                ) AS payment_bills
                ON datesAndAmount.yearAndMonth = payment_bills.yearAndMonth
                
                GROUP BY payment_bills.yearAndMonth
        
            ) AS withBills
            ON withBills.yearAndMonth = dates.yearAndMonth
            
            ORDER BY dates.yearAndMonth ASC
        ";

        $stmt = $connection->executeQuery($sql);
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @return array
     * @throws DBALException
     */
    public function fetchTotalPaymentsAmountForTypes(): array {

        $connection = $this->em->getConnection();

        $sql = "
            SELECT 
            ROUND(SUM(mpm.money), 2)            AS amount,
            mps.value                      AS type,
            DATE_FORMAT(mpm.date, '%Y-%m') AS `date`
            
            FROM my_payment_monthly mpm
            
            JOIN my_payment_setting mps
            ON mpm.type_id = mps.id
            AND name       = 'type'
            
            WHERE 1
            AND mpm.deleted = 0
            
            GROUP BY mpm.type_id, DATE_FORMAT(mpm.date, '%Y-%m')
            ORDER BY DATE_FORMAT(mpm.date, '%Y-%m') ASC
        ";

        $stmt    = $connection->executeQuery($sql);
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @return MyPaymentsOwed[]
     */
    public function fetchHistoricalOwedMoney(): array
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder->select('mpo')
            ->from(MyPaymentsOwed::class, 'mpo')
            ->where('mpo.deleted = 1')
            ->orderBy("mpo.date", "DESC");

        $query   = $queryBuilder->getQuery();
        $results = $query->execute();

        return $results;
    }

}