<?php

namespace App\Repository\Modules\Schedules;

use App\DTO\Modules\Schedules\IncomingScheduleDTO;
use App\Entity\Modules\Schedules\MySchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use PDO;

/**
 * @method MySchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method MySchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method MySchedule[]    findAll()
 * @method MySchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyScheduleRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MySchedule::class);
    }

    /**
     * Will return incoming schedules information array
     *
     * @param int $maxDaysOld
     *
     * @return IncomingScheduleDTO[]
     * @throws Exception
     */
    public function getIncomingSchedulesInformationInDays(int $maxDaysOld): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                sch.id                      AS id,
                sch.title                   AS title,
                sch.start                   AS date,
                DATEDIFF(sch.start ,NOW())  AS daysDiff,
                mchc.icon                   AS icon,
                sch.body                    AS body

            FROM my_schedule sch
            
            JOIN my_schedule_calendar mchc
            ON mchc.id = sch.calendar_id
            
            WHERE 
            DATEDIFF(sch.start ,NOW()) >= :maxDaysOld
            AND sch.deleted  = 0
            AND mchc.deleted = 0
            
            ORDER BY DATEDIFF(sch.start ,NOW()) DESC
        ";

        $bindedValues = [
            'maxDaysOld' => -$maxDaysOld,
        ];

        $parametersTypes = [
            Types::INTEGER,
        ];

        $statement = $connection->executeQuery($sql, $bindedValues, $parametersTypes);
        $results   = $statement->fetchAll(PDO::FETCH_CLASS, IncomingScheduleDTO::class);

        return $results;
    }

    /**
     * Will return incoming schedules information array for a schedules which have incoming/past (unhandled) reminders
     *
     * @return IncomingScheduleDTO[]
     * @throws Exception
     */
    public function getSchedulesWithRemindersInformation(): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                sch.id                      AS id,
                msr.id                      AS reminderId,                   
                sch.title                   AS title,
                sch.start                   AS date,
                DATEDIFF(sch.start ,NOW())  AS daysDiff,
                mchc.icon                   AS icon,
                sch.body                    AS body

            FROM my_schedule sch
            
            JOIN my_schedule_calendar mchc
            ON mchc.id = sch.calendar_id

            JOIN my_schedule_reminder msr
            ON msr.schedule_id = sch.id
            AND msr.deleted = 0
            AND msr.processed = 0

            WHERE 
            DATE_FORMAT(msr.date, '%Y-%m-%d') <= DATE_FORMAT(NOW(), '%Y-%m-%d')
            AND sch.deleted  = 0
            AND mchc.deleted = 0
        ";

        $statement = $connection->executeQuery($sql);
        $results   = $statement->fetchAll(PDO::FETCH_CLASS, IncomingScheduleDTO::class);

        return $results;
    }

    /**
     * @return Array<MySchedule>
     */
    public function findForDashboard(): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("s")
            ->from(MySchedule::class, "s")
            ->where("s.deleted = 0")
            ->orderBy("s.start", "DESC");

        return $qb->getQuery()->getResult();
    }

}
