<?php

namespace App\Repository\Modules\Schedules;

use App\Entity\Modules\Schedules\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Schedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schedule[]    findAll()
 * @method Schedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyScheduleRepository extends ServiceEntityRepository {

    const KEY_NAME          = 'name';
    const KEY_DATE          = 'date';
    const KEY_ICON          = 'icon';
    const KEY_INFORMATION   = 'information';
    const KEY_SCHEDULE_TYPE = 'scheduleType';

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Schedule::class);
    }


    //todo: add schedule logic here later on
}
