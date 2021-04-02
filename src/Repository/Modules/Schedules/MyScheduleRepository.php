<?php

namespace App\Repository\Modules\Schedules;

use App\DTO\Modules\Schedules\IncomingScheduleDTO;
use App\Entity\Modules\Schedules\MySchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use PDO;

/**
 * @method MySchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method MySchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method MySchedule[]    findAll()
 * @method MySchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyScheduleRepository extends ServiceEntityRepository {

    const KEY_NAME          = 'name';
    const KEY_DATE          = 'date';
    const KEY_ICON          = 'icon';
    const KEY_INFORMATION   = 'information';
    const KEY_SCHEDULE_TYPE = 'scheduleType';

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MySchedule::class);
    }

    /**
     * Will save schedule or update the existing one
     *
     * @param MySchedule $schedule
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveSchedule(MySchedule $schedule): void
    {
        $this->_em->persist($schedule);
        $this->_em->flush();
    }

    /**
     * Will return all not deleted schedules
     *
     * @return MySchedule[]
     */
    public function getAllNotDeletedSchedules(): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select("sch")
            ->from(MySchedule::class, "sch")
            ->where("sch.deleted = 0");

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MySchedule|null
     * @throws NonUniqueResultException
     */
    public function findOneScheduleById(int $id): ?MySchedule
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select("sch")
            ->from(MySchedule::class, "sch")
            ->where("sch.id = :id")
            ->andWhere("sch.deleted = 0")
            ->setParameter("id", $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Will return incoming schedules information array
     *
     * @param int $days
     * @param int|null $limit
     * @return IncomingScheduleDTO[]
     * @throws Exception
     */
    public function getIncomingSchedulesInformationInDays(int $days, ?int $limit = null): array
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
            sch.start < NOW() + INTERVAL :days DAY -- include past
            AND sch.deleted  = 0
            AND mchc.deleted = 0
        ";

        $bindedValues = [
            'days' => $days,
        ];

        $parametersTypes = [
            Types::INTEGER,
        ];

        if( !is_null($limit) ){
            $sql .= " LIMIT {$limit}"; // limit cannot be simply parametrized, won't work also with params types
        }

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


}
