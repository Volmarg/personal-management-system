<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsOwed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyPaymentsOwed|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsOwed|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsOwed[]    findAll()
 * @method MyPaymentsOwed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsOwedRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyPaymentsOwed::class);
    }

    /**
     * This only gets summary SUM(), not fetching any detailed data.
     * @param bool $owed_by_me
     * @throws DBALException
     */
    public function getMoneyOwedSummaryForTargetsAndOwningSide(bool $owed_by_me){

        $connection = $this->_em->getConnection();

        $sql = "
            SELECT 
                target      AS target,
                SUM(amount) AS amount,
                owed_by_me  AS owedByMe
            
            FROM my_payment_owed
            WHERE 1 
                AND owed_by_me  = :owed_by_me
                AND deleted     = 0
            GROUP BY target;
        ";

        $binded_values = [
            'owed_by_me' => $owed_by_me
        ];


        $statement = $connection->prepare($sql);
        $statement->execute($binded_values);
        $results = $statement->fetchAll();

        return $results;
    }
}
