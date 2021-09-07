<?php

namespace App\Controller\Modules\Schedules;

use App\Controller\Core\Application;
use App\Entity\Modules\Schedules\MyScheduleReminder;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyScheduleRemindersController extends AbstractController {
    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
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
        $this->app->repositories->myScheduleReminderRepository->saveReminder($reminder);
    }

    /**
     * Will return all not deleted reminders
     *
     * @return MyScheduleReminder[]
     */
    public function getAllNotDeletedReminders(): array
    {
        return $this->app->repositories->myScheduleReminderRepository->getAllNotDeletedReminders();
    }

    /**
     * Will return one reminder or null if none is found for id
     *
     * @param int $id
     * @return MyScheduleReminder|null
     */
    public function findOneById(int $id): ?MyScheduleReminder
    {
        return $this->app->repositories->myScheduleReminderRepository->findOneById($id);
    }

    /**
     * Will remove the reminder entity
     *
     * @param MyScheduleReminder $reminder
     * @throws ORMException
     */
    public function removeReminder(MyScheduleReminder $reminder): void
    {
        $this->app->repositories->myScheduleReminderRepository->removeReminder($reminder);
    }

}
