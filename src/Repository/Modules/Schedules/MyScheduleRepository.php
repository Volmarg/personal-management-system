<?php

namespace App\Repository\Modules\Schedules;

use App\Entity\Modules\Schedules\MySchedule;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

}
