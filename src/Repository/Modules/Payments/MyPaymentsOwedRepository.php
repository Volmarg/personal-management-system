<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsOwed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPaymentsOwed|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsOwed|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsOwed[]    findAll()
 * @method MyPaymentsOwed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsOwedRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyPaymentsOwed::class);
    }

    /**
     * This only gets summary SUM(), not fetching any detailed data.
     * @param bool $owedByMe
     * @throws DBALException
     */
    public function getMoneyOwedSummaryForTargetsAndOwningSide(bool $owedByMe)
    {

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT 
                target      AS target,
                SUM(amount) AS amount,
                owed_by_me  AS owedByMe,
                currency    AS currency
            
            FROM my_payment_owed
            WHERE 1 
                AND owed_by_me  = :owed_by_me
                AND deleted     = 0
            GROUP BY target, currency;
        ";

        $bindedValues = [
            'owed_by_me' => $owedByMe
        ];


        $statement = $connection->prepare($sql);
        $statement->execute($bindedValues);
        $results = $statement->fetchAll();

        return $results;
    }

    /**
     * Returns total summary how much I owed to someone or someone to me
     * @return array
     * @throws DBALException
     */
    public function fetchSummaryWhoOwesHowMuch(): array
    {
        $connection = $this->_em->getConnection();

        $sql = "
            SELECT 
            target AS target,
            ROUND (
                SUM(
                    IF(owed_by_me, -amount, amount)
                ),2 
            ) AS amount,
            IF(
                SUM(
                    IF(owed_by_me, -amount, amount)
                )<0, 1, 0
            ) AS summaryOwedByMe,
            currency AS currency
            
            FROM my_payment_owed
            WHERE 1 
            AND deleted = 0
            GROUP BY target, currency;
        ";

        $stmt    = $connection->executeQuery($sql);
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsOwed|null
     */
    public function findOneById(int $id): ?MyPaymentsOwed
    {
        return $this->find($id);
    }

    /**
     * Will return all not deleted entities but filtered by the owed column
     *
     * @param bool $owedByMe
     * @return array
     */
    public function findAllNotDeletedFilteredByOwedStatus(bool $owedByMe): array
    {
        return $this->findBy([
            'deleted'  => 0,
            'owedByMe' => $owedByMe,
        ]);
    }

}
