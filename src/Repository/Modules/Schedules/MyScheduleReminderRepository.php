<?php

namespace App\Repository\Modules\Schedules;

use App\Entity\Modules\Schedules\MyScheduleReminder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveReminder(MyScheduleReminder $reminder): void
    {
        $this->_em->persist($reminder);
        $this->_em->flush();
    }

    /**
     * Will return all not deleted reminders
     *
     * @return MyScheduleReminder[]
     */
    public function getAllNotDeletedReminders(): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select("mschr")
            ->from(MyScheduleReminder::class, "mschr")
            ->where("mschr.deleted = 0");

        $results = $queryBuilder->getQuery()->execute();
        return $results;
    }

    /**
     * Will remove the reminder entity
     *
     * @param MyScheduleReminder $reminder
     * @throws ORMException
     */
    public function removeReminder(MyScheduleReminder $reminder): void
    {
        $this->_em->remove($reminder);
        $this->_em->flush();
    }

}
