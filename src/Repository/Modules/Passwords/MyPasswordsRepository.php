<?php

namespace App\Repository\Modules\Passwords;

use App\Entity\Modules\Passwords\MyPasswords;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPasswords|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPasswords|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPasswords[]    findAll()
 * @method MyPasswords[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPasswordsRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyPasswords::class);
    }

    /**
     * @return MyPasswords[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->findBy(['deleted' => 0]);
    }
}