<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class UserRepository extends ServiceEntityRepository {

    const FIELD_EMAIL = "email";

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, User::class);
    }

    /**
     * @param User $user
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveUser(User $user){
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param string $email
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeByEmail(string $email)
    {
        $entity = $this->findOneBy([self::FIELD_EMAIL => $email]);

        if( !empty($entity) ){
            $this->_em->remove($entity);
            $this->_em->flush();
        }
    }
}
