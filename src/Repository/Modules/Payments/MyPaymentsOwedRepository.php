<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsOwed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsOwed|null
     */
    public function findOneById(int $id): ?MyPaymentsOwed
    {
        return $this->find($id);
    }

}
