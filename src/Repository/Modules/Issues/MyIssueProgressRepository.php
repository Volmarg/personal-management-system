<?php

namespace App\Repository\Modules\Issues;

use App\Entity\Modules\Issues\MyIssueProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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

    /**
     * @param MyIssueProgress $issueProgress
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function saveIssueProgress(MyIssueProgress $issueProgress)
    {
        $this->_em->persist($issueProgress);
        $this->_em->flush($issueProgress);
    }
}
