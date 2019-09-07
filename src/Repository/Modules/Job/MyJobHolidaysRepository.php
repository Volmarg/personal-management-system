<?php

namespace App\Repository\Modules\Job;

use App\Entity\Modules\Job\MyJobHolidays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyJobHolidays|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyJobHolidays|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyJobHolidays[]    findAll()
 * @method MyJobHolidays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyJobHolidaysRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyJobHolidays::class);
    }

}
