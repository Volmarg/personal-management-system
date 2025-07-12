<?php

namespace App\Repository\Modules\Schedules;

use App\Entity\Modules\Schedules\MyScheduleReminder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyScheduleReminder|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyScheduleReminder|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyScheduleReminder[]    findAll()
 * @method MyScheduleReminder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyScheduleReminderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyScheduleReminder::class);
    }

    /**
     * Will return one reminder or null if none is found for id
     * @param int $id
     * @return MyScheduleReminder|null
     */
    public function findOneById(int $id): ?MyScheduleReminder
    {
        return $this->find($id);
    }

    /**
     * Will save reminder or update the existing one
     *
     * @param MyScheduleReminder $reminder
     */
    public function saveReminder(MyScheduleReminder $reminder): void
    {
        $this->_em->persist($reminder);
        $this->_em->flush();
    }

    /**
     * Will remove the reminder entity
     *
     * @param MyScheduleReminder $reminder
     */
    public function removeReminder(MyScheduleReminder $reminder): void
    {
        $this->_em->remove($reminder);
        $this->_em->flush();
    }

}
