<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsIncome;
use App\Repository\AbstractRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPaymentsIncome|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsIncome|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsIncome[]    findAll()
 * @method MyPaymentsIncome[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsIncomeRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyPaymentsIncome::class);
    }

    /**
     * Returns all not deleted entities
     *
     * @return MyPaymentsIncome[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->findBy([AbstractRepository::FIELD_DELETED => 0]);
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsIncome|null
     */
    public function findOneById(int $id): ?MyPaymentsIncome
    {
        return $this->find($id);
    }

}
