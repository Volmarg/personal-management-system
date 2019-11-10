<?php

namespace App\Repository\Modules\Contacts2;

use App\Entity\Modules\Contacts2\MyContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContact[]    findAll()
 * @method MyContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyContactRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyContact::class);
    }

    /**
     * @return MyContact[]
     */
    public function findAllNotDeleted():array {
        return $this->findBy(['deleted' => 0]);
    }

}
