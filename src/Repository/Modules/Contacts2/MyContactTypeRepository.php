<?php

namespace App\Repository\Modules\Contacts2;

use App\Entity\Modules\Contacts2\MyContactType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyContactType|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContactType|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContactType[]    findAll()
 * @method MyContactType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactTypeRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyContactType::class);
    }

    /**
     * @return MyContactType[]
     */
    public function getAllNotDeleted():array {
        return $this->findBy(['deleted' => 0]);
    }

}