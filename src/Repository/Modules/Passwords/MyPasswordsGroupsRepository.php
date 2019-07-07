<?php

namespace App\Repository\Modules\Passwords;

use App\Entity\Modules\Passwords\MyPasswordsGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyPasswordsGroups|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPasswordsGroups|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPasswordsGroups[]    findAll()
 * @method MyPasswordsGroups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPasswordsGroupsRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyPasswordsGroups::class);
    }

}