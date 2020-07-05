<?php

namespace App\Repository\Modules\Schedules;

use App\Entity\Modules\Schedules\MyScheduleType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyScheduleType|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyScheduleType|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyScheduleType[]    findAll()
 * @method MyScheduleType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyScheduleTypeRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyScheduleType::class);
    }

    /**
     * @return MyScheduleType[]
     */
    public function getAllNonDeletedTypes(): array {
        return $this->findBy([MyScheduleType::FIELD_NAME => 0]);
    }

}
