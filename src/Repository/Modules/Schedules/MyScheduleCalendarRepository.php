<?php

namespace App\Repository\Modules\Schedules;

use App\Entity\Modules\Schedules\MyScheduleCalendar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyScheduleCalendar|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyScheduleCalendar|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyScheduleCalendar[]    findAll()
 * @method MyScheduleCalendar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyScheduleCalendarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyScheduleCalendar::class);
    }
}
