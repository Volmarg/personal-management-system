<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyRecurringPaymentMonthly|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyRecurringPaymentMonthly|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyRecurringPaymentMonthly[]    findAll()
 * @method MyRecurringPaymentMonthly[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyRecurringPaymentMonthlyRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyRecurringPaymentMonthly::class);
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MyRecurringPaymentMonthly|null
     */
    public function findOneById(int $id): ?MyRecurringPaymentMonthly
    {
        return $this->find($id);
    }

    /**
     * Will return all not deleted records
     *
     * @param string $sort_by_column
     * @param string $sort_direction
     * @return MyRecurringPaymentMonthly[]
     */
    public function getAllNotDeleted(string $sort_by_column, string $sort_direction = "ASC")
    {
        return $this->findBy(['deleted' => '0'], [$sort_by_column => $sort_direction]);
    }

}
