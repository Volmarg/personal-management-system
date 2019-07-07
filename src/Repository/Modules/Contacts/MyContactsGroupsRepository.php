<?php

namespace App\Repository\Modules\Contacts;

use App\Entity\Modules\Contacts\MyContactsGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyContactsGroups|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContactsGroups|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContactsGroups[]    findAll()
 * @method MyContactsGroups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactsGroupsRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyContactsGroups::class);
    }

}