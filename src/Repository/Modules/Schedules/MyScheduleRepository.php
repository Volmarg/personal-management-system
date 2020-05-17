<?php

namespace App\Repository\Modules\Schedules;

use App\Entity\Modules\Schedules\MySchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

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
    const KEY_DAYS_DIFF     = 'daysDiff';
    const KEY_SCHEDULE_TYPE = 'scheduleType';

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MySchedule::class);
    }

    public function getIncomingSchedulesInDays(int $days, bool $include_past = true){

        $connection = $this->getEntityManager()->getConnection();

        if( $include_past ){
            $records_interval_sql = "
                mch.date < NOW() + INTERVAL :days DAY
            ";
        }else{
            $records_interval_sql = "
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
                mcht.icon                   AS :icon

            FROM my_schedule mch
            
            JOIN my_schedule_type mcht
            ON mcht.id = mch.schedule_type_id
            
            WHERE 
            $records_interval_sql
            AND mch.deleted  = 0
            AND mcht.deleted = 0
        ";

        $binded_values = [
          'name'         => self::KEY_NAME,
          'date'         => self::KEY_DATE,
          'daysDiff'     => self::KEY_DAYS_DIFF,
          'scheduleType' => self::KEY_SCHEDULE_TYPE,
          'icon'         => self::KEY_ICON,
          'days'         => $days
        ];

        $statement = $connection->executeQuery($sql, $binded_values);
        $results   = $statement->fetchAll();

        return $results;
    }

    /**
     * @param string $schedules_type_name
     * @return MySchedule[]
     */
    public function getSchedulesByScheduleTypeName(string $schedules_type_name):array
    {
        $qb = $this->createQueryBuilder('sch');

        $qb->select('sch')
            ->join('sch.scheduleType', 'scht')
            ->where('sch.deleted = 0')
            ->andWhere('scht.deleted = 0')
            ->andWhere('scht.name = :schedules_type_name')
            ->setParameter('schedules_type_name', $schedules_type_name);

        $query      = $qb->getQuery();
        $results    = $query->getResult();

        return $results;
    }
}
