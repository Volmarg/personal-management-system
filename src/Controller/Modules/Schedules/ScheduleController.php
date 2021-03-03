<?php


namespace App\Controller\Modules\Schedules;


use App\Controller\Core\Application;
use App\Entity\Modules\Schedules\Schedule;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ScheduleController extends AbstractController
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
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
        $this->app->repositories->scheduleRepository->saveSchedule($schedule);
    }

    /**
     * Will return all not deleted schedules
     *
     * @return Schedule[]
     */
    public function getAllNotDeletedSchedules(): array
    {
        return $this->app->repositories->scheduleRepository->getAllNotDeletedSchedules();
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
        return $this->app->repositories->scheduleRepository->findOneScheduleById($id);
    }

}