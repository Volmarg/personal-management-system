<?php

namespace App\Repository\Modules\Issues;

use App\Entity\Modules\Issues\MyIssueProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyIssueProgress|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyIssueProgress|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyIssueProgress[]    findAll()
 * @method MyIssueProgress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyIssueProgressRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyIssueProgress::class);
    }
}
