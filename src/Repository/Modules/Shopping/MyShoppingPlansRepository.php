<?php

namespace App\Repository\Modules\Shopping;

use App\Entity\Modules\Shopping\MyShoppingPlans;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyShoppingPlans|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyShoppingPlans|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyShoppingPlans[]    findAll()
 * @method MyShoppingPlans[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyShoppingPlansRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyShoppingPlans::class);
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MyShoppingPlans|null
     */
    public function findOneById(int $id): ?MyShoppingPlans
    {
        return $this->find($id);
    }

}
