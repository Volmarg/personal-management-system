<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPaymentsProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsProduct[]    findAll()
 * @method MyPaymentsProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsProductRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyPaymentsProduct::class);
    }

    /**
     * Will return all not deleted entries
     *
     * @return MyPaymentsProduct[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->findBy(['deleted' => 0]);
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsProduct|null
     */
    public function findOneById(int $id): ?MyPaymentsProduct
    {
        return $this->find($id);
    }

}
