<?php

namespace App\Repository\Modules\Contacts;

use App\Entity\Modules\Contacts\MyContacts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyContacts|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContacts|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContacts[]    findAll()
 * @method MyContacts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyContacts::class);
    }

}
