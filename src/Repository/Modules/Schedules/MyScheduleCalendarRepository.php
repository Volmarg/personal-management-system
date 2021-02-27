<?php

namespace App\Repository\Modules\Schedules;

use App\DTO\Modules\Schedules\ScheduleCalendarDTO;
use App\Entity\Modules\Schedules\MyScheduleCalendar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PDO;

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

    /**
     * Will fetch all non deleted calendars
     * using raw sql for performance gain
     *
     * @throws \Doctrine\DBAL\Exception
     * @return ScheduleCalendarDTO[]
     */
    public function fetchAllNonDeletedCalendarsData(): array
    {
        $connection = $this->_em->getConnection();

        $sql = "
            SELECT
            id                    AS id,
            name                  AS name,
            color                 AS color,
            background_color      AS backgroundColor,
            drag_background_color AS dragBackgroundColor,
            border_color          AS borderColor,
            deleted               AS deleted,
            icon                  AS icon
            
            FROM my_schedule_calendar

            WHERE deleted = 0
        ";

        $results = $connection->executeQuery($sql)->fetchAll(PDO::FETCH_CLASS, ScheduleCalendarDTO::class);

        if( empty($results) ){
            return [];
        }

        return $results;
    }

    /**
     * Will return calendar entity for provided id or null if nothing is found
     *
     * @param string $id
     * @return MyScheduleCalendar|null
     */
    public function findCalendarById(string $id): ?MyScheduleCalendar
    {
        return $this->find($id);
    }
}
