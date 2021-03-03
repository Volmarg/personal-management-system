<?php

namespace App\Repository\Modules\Schedules;

use App\DTO\Modules\Schedules\IncomingScheduleDTO;
use App\Entity\Modules\Schedules\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

// todo: rename + entity to my_schedule

/**
 * @method Schedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schedule[]    findAll()
 * @method Schedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    /**
     * Will save schedule or update the existing one
     *
     * @param Schedule $schedule
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveSchedule(Schedule $schedule): void
    {
        $this->_em->persist($schedule);
        $this->_em->flush();
    }

    /**
     * Will return all not deleted schedules
     *
     * @return Schedule[]
     */
    public function getAllNotDeletedSchedules(): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select("sch")
            ->from(Schedule::class, "sch")
            ->where("sch.deleted = 0");

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return Schedule|null
     * @throws NonUniqueResultException
     */
    public function findOneScheduleById(int $id): ?Schedule
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select("sch")
            ->from(Schedule::class, "sch")
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
                sch.title                   AS title,
                sch.start                   AS date,
                DATEDIFF(sch.start ,NOW())  AS daysDiff,
                mchc.icon                   AS icon,
                sch.body                    AS body

            FROM schedule sch
            
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
            $sql .= " LIMIT {$limit}"; //todo
            $bindedValues["limit"] = $limit;
            $parametersTypes[]     = \PDO::PARAM_INT;
        }

        $statement = $connection->executeQuery($sql, $bindedValues, $parametersTypes);
        $results   = $statement->fetchAll(\PDO::FETCH_CLASS, IncomingScheduleDTO::class);

        return $results;
    }

}
