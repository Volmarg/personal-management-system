<?php

namespace App\Repository\Modules\Contacts2;

use App\Entity\Modules\Contacts2\MyContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContact[]    findAll()
 * @method MyContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyContact::class);
    }

    /**
     * @return MyContact[]
     */
    public function findAllNotDeleted():array {
        return $this->findBy(['deleted' => 0]);
    }

    /**
     * This function flushes the $entity
     * @param MyContact $my_contact
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveEntity(MyContact $my_contact){
        $this->_em->persist($my_contact);
        $this->_em->flush();
    }

    /**
     * This function will search for single (not deleted) entity with given id
     * @param int $id
     * @return MyContact|null
     */
    public function findOneById(int $id):?MyContact {
        return $this->findOneBy(['id' => $id]);
    }

}
