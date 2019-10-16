<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsBillsItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyPaymentsBillsItems|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsBillsItems|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsBillsItems[]    findAll()
 * @method MyPaymentsBillsItems[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsBillsItemsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyPaymentsBillsItems::class);
    }

    // /**
    //  * @return MyPaymentsBillsItems[] Returns an array of MyPaymentsBillsItems objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MyPaymentsBillsItems
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
