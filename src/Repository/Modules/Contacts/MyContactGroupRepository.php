<?php

namespace App\Repository\Modules\Contacts;

use App\Entity\Modules\Contacts\MyContactGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyContactGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContactGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContactGroup[]    findAll()
 * @method MyContactGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactGroupRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
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
    public function getOneByName(string $name):?MyContactGroup {
        return $this->findOneBy( ["name" => $name] );
    }

    /**
     * Will return one entity for given id or null if nothing was found
     *
     * @param int $id
     * @return MyContactGroup|null
     */
    public function getOneById(int $id): ?MyContactGroup
    {
        return $this->find($id);
    }

}