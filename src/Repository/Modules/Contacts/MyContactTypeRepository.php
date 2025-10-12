<?php

namespace App\Repository\Modules\Contacts;

use App\Entity\Modules\Contacts\MyContactType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyContactType|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContactType|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContactType[]    findAll()
 * @method MyContactType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactTypeRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyContactType::class);
    }

    /**
     * @return MyContactType[]
     */
    public function getAllNotDeleted():array {
        return $this->findBy(['deleted' => 0]);
    }

    /**
     * @param string $name
     * @return MyContactType|null
     */
    public function getOneByName(string $name):?MyContactType
    {
        $results = $this->findBy(["name" => $name]);

        if( empty($results) ){
            return null;
        }

        return $results[0];
    }

    /**
     * Will return one entity for given id, if none was found then null wil be returned
     *
     * @param int $id
     * @return MyContactType|null
     */
    public function findOneById(int $id): ?MyContactType
    {
        return $this->find($id);
    }

}