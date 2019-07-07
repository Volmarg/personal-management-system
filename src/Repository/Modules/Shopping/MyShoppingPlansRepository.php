<?php

namespace App\Repository\Modules\Shopping;

use App\Entity\Modules\Shopping\MyShoppingPlans;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyShoppingPlans|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyShoppingPlans|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyShoppingPlans[]    findAll()
 * @method MyShoppingPlans[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyShoppingPlansRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyShoppingPlans::class);
    }

}
