<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class UserRepository extends ServiceEntityRepository {

    const FIELD_EMAIL = "email";

    public function __construct(ManagerRegistry $registry) {
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

    /**
     * Will return all existing users
     * @return User[]
     */
    public function getAllUsers(): array
    {
        return $this->findAll();
    }

    /**
     * Will return one user for given username
     * or if no user was found then null is being returned
     * @param string $username
     * @return User|null
     */
    public function findOneByName(string $username): ?User
    {
        $entity = $this->findOneBy([
           User::USERNAME_FIELD => $username,
        ]);

        return $entity;
    }

    /**
     * Will return one user for given email
     * or if no user was found then null is being returned
     * @param string $email
     * @return User|null
     */
    public function findOneByEmail(string $email): ?User
    {
        $entity = $this->findOneBy([
           User::EMAIL_FIELD => $email,
        ]);

        return $entity;
    }

}
