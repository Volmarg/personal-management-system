<?php

namespace App\Repository\Modules\Job;

use App\Entity\Modules\Job\MyJobSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyJobSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyJobSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyJobSettings[]    findAll()
 * @method MyJobSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyJobSettingsRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyJobSettings::class);
    }

}
