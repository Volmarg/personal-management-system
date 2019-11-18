<?php

namespace App\Repository\Modules\Contacts;

use App\Entity\Modules\Contacts\MyContactGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyContactGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContactGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContactGroup[]    findAll()
 * @method MyContactGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactGroupRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyContactGroup::class);
    }

    /**
     * @return MyContactGroup[]
     */
    public function getAllNotDeleted():array {
        return $this->findBy(['deleted' => 0]);
    }

    /**
     * @param string $name
     * @return MyContactGroup|null
     */
    public function getOneNonDeletedByName(string $name):?MyContactGroup {
        return $this->findOneBy( ["name" => $name] );
    }

}