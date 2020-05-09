<?php

namespace App\Repository\Modules\Issues;

use App\Entity\Modules\Issues\MyIssueContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyIssueContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyIssueContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyIssueContact[]    findAll()
 * @method MyIssueContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyIssueContactRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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

}
