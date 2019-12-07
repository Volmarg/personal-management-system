<?php

namespace App\Repository\Modules\Goals;

use App\Entity\Modules\Goals\MyGoalsPayments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyGoalsPayments|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyGoalsPayments|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyGoalsPayments[]    findAll()
 * @method MyGoalsPayments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyGoalsPaymentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyGoalsPayments::class);
    }

    public function getGoalsPayments(){
        $results = $this->findBy([
            'displayOnDashboard' => 1,
            'deleted'            => 0
        ]);
        return $results;
    }
}
