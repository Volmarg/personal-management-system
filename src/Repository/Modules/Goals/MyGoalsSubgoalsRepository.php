<?php

namespace App\Repository\Modules\Goals;

use App\Entity\Modules\Goals\MyGoalsSubgoals;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyGoalsSubgoals|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyGoalsSubgoals|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyGoalsSubgoals[]    findAll()
 * @method MyGoalsSubgoals[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyGoalsSubgoalsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyGoalsSubgoals::class);
    }

}
