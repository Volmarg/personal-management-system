<?php

namespace App\Repository\Modules\Passwords;

use App\Entity\Modules\Passwords\MyPasswordsGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPasswordsGroups|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPasswordsGroups|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPasswordsGroups[]    findAll()
 * @method MyPasswordsGroups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPasswordsGroupsRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyPasswordsGroups::class);
    }

    /**
     * Will return all not deleted entities
     * @return MyPasswordsGroups[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->findBy(['deleted' => 0]);
    }

    /**
     * Will return single entity for given id, otherwise null is returned
     *
     * @param int $id
     * @return MyPasswordsGroups|null
     */
    public function findOneById(int $id): ?MyPasswordsGroups
    {
        return $this->find($id);
    }

    /**
     * Will return single entity for given id, otherwise null is returned
     *
     * @param string $name
     * @return MyPasswordsGroups|null
     */
    public function findOneByName(string $name): ?MyPasswordsGroups
    {
        $results = $this->findBy(["name" => $name]);

        if( empty($results) ){
            return null;
        }

        return $results[0];
    }

}