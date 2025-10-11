<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class UserRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, User::class);
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

    /**
     * @return User|null
     */
    public function findOneActive(): ?User
    {
        return $this->findOneBy(['enabled' => true]);
    }
}
