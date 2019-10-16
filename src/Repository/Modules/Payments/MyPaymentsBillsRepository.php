<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsBills;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyPaymentsBills|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsBills|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsBills[]    findAll()
 * @method MyPaymentsBills[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsBillsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyPaymentsBills::class);
    }

}
