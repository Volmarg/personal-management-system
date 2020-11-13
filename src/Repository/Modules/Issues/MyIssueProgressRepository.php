<?php

namespace App\Repository\Modules\Issues;

use App\Entity\Modules\Issues\MyIssueProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyIssueProgress|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyIssueProgress|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyIssueProgress[]    findAll()
 * @method MyIssueProgress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyIssueProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    /**
     * Will return entity for given id, otherwise null if nothing was found
     *
     * @param int $id
     * @return MyIssueProgress|null
     */
    public function findOneById(int $id): ?MyIssueProgress
    {
        return $this->find($id);
    }

}
