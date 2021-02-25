<?php

namespace App\Repository\Modules\Schedules;

use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Entity\Modules\Schedules\MySchedule;
use App\Entity\Modules\Schedules\Schedule;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

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
    const KEY_DAYS_DIFF     = 'daysDiff';
    const KEY_SCHEDULE_TYPE = 'scheduleType';

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MySchedule::class);
    }

    /**
     * Will return schedules entities incoming in days
     *
     * @param int $days
     * @return MySchedule[]
     */
    public function getIncomingSchedulesEntitiesInDays(int $days): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $now    = new DateTime();
        $future = (new DateTime())->modify("+{$days} DAYS");

        $queryBuilder->select("mch")
            ->from(MySchedule::class, "mch")
            ->where("mch.deleted = 0")
            ->andWhere("mch.Date BETWEEN :now AND :future")
            ->setParameter("now", $now)
            ->setParameter("future", $future);

        $result = $queryBuilder->getQuery()->execute();

        return $result;
    }

    /**
     * Will return incoming schedules information array
     *
     * @param int $days
     * @param bool $includePast
     * @return array
     * @throws Exception
     */
    public function getIncomingSchedulesInformationInDays(int $days, bool $includePast = true): array
    {
        $connection = $this->getEntityManager()->getConnection();

        if( $includePast ){
            $recordsIntervalSql = "
                mch.date < NOW() + INTERVAL :days DAY
            ";
        }else{
            $recordsIntervalSql = "
                AND DATEDIFF (mch.date, NOW()) > 0
                mch.date BETWEEN NOW() AND NOW() + INTERVAL :days DAY
            ";
        }

        $sql = "
            SELECT 
                mch.name                    AS :name,
                mch.date                    AS :date,
                DATEDIFF(mch.date ,NOW())   AS :daysDiff,
                mcht.name                   AS :scheduleType,
                mcht.icon                   AS :icon,
                mch.information             AS :information

            FROM my_schedule mch
            
            JOIN my_schedule_type mcht
            ON mcht.id = mch.schedule_type_id
            
            WHERE 
            $recordsIntervalSql
            AND mch.deleted  = 0
            AND mcht.deleted = 0
        ";

        $bindedValues = [
          'name'         => self::KEY_NAME,
          'date'         => self::KEY_DATE,
          'daysDiff'     => self::KEY_DAYS_DIFF,
          'scheduleType' => self::KEY_SCHEDULE_TYPE,
          'icon'         => self::KEY_ICON,
          'information'  => self::KEY_INFORMATION,
          'days'         => $days
        ];

        $statement = $connection->executeQuery($sql, $bindedValues);
        $results   = $statement->fetchAll();

        return $results;
    }

    /**
     * @param string $schedulesTypeName
     * @return MySchedule[]
     */
    public function getSchedulesByScheduleTypeName(string $schedulesTypeName):array
    {
        $qb = $this->createQueryBuilder('sch');

        $qb->select('sch')
            ->join('sch.scheduleType', 'scht')
            ->where('sch.deleted = 0')
            ->andWhere('scht.deleted = 0')
            ->andWhere('scht.name = :schedules_type_name')
            ->setParameter('schedules_type_name', $schedulesTypeName);

        $query      = $qb->getQuery();
        $results    = $query->getResult();

        return $results;
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MySchedule|null
     */
    public function findOneById(int $id): ?MySchedule
    {
        return $this->find($id);
    }


    // TODO: New schedules logic

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

}
