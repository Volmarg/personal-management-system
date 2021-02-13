<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPaymentsSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsSettings[]    findAll()
 * @method MyPaymentsSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsSettingsRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyPaymentsSettings::class);
    }

    public function fetchCurrencyMultiplier() {
        $result = $this->findBy(['name' => 'currency_multiplier']);
        return (empty($result) ? null : $result[0]->getValue());
    }

    public function fetchCurrencyMultiplierRecord() {
        return $this->findBy(['name' => 'currency_multiplier']);
    }

    public function getAllPaymentsTypes() {
        return $this->findBy([
            'name'    => 'type',
            'deleted' => 0
        ]);
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsSettings|null
     */
    public function findOneById(int $id): ?MyPaymentsSettings
    {
       return $this->find($id);
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param string $value
     * @return MyPaymentsSettings | null
     */
    public function findOneByValue(string $value): ?MyPaymentsSettings
    {
        $result = $this->findBy(['value' => $value]);

        if( empty($result) ){
            return null;
        }

        return $result[0];
    }

    /**
     * Will return array of years for payments
     *
     * @return string[]
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getYears(): array
    {
        $connection = $this->_em->getConnection();

        $sql = " 
            SELECT DATE_FORMAT(date, '%Y') AS year
            FROM my_payment_monthly
            GROUP BY DATE_FORMAT(date, '%Y')
            ORDER BY DATE_FORMAT(date, '%Y') DESC;
        ";

        $stmt    = $connection->executeQuery($sql);
        $results = $stmt->fetchAllAssociative();
        $years   = array_column($results, 'year');

        return $years;
    }

}
