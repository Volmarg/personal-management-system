<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentCurrency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MyPaymentCurrency|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentCurrency|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentCurrency[]    findAll()
 * @method MyPaymentCurrency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentCurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyPaymentCurrency::class);
    }

    // /**
    //  * @return MyPaymentCurrency[] Returns an array of MyPaymentCurrency objects
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
    public function findOneBySomeField($value): ?MyPaymentCurrency
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
