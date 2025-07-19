<?php

namespace App\Repository\Modules\Issues;

use App\Entity\Modules\Issues\MyIssueContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyIssueContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyIssueContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyIssueContact[]    findAll()
 * @method MyIssueContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyIssueContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyIssueContact::class);
    }

}
