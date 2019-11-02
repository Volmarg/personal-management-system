<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyRecurringPaymentMonthly|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyRecurringPaymentMonthly|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyRecurringPaymentMonthly[]    findAll()
 * @method MyRecurringPaymentMonthly[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyRecurringPaymentMonthlyRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyRecurringPaymentMonthly::class);
    }

}
