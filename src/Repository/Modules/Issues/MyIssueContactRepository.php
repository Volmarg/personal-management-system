<?php

namespace App\Repository\Modules\Issues;

use App\Entity\Modules\Issues\MyIssueContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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

    /**
     * @param MyIssueContact $issueContact
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveIssueContact(MyIssueContact $issueContact)
    {
        $this->_em->persist($issueContact);
        $this->_em->flush($issueContact);
    }

    /**
     * Will return entity for given id, otherwise null if nothing was found
     *
     * @param int $id
     * @return MyIssueContact|null
     */
    public function findOneById(int $id): ?MyIssueContact
    {
        return $this->find($id);
    }

}
