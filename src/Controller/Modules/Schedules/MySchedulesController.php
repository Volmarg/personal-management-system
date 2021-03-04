<?php

namespace App\Controller\Modules\Schedules;

use App\Controller\Core\Application;
use App\Entity\Modules\Schedules\MySchedule;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MySchedulesController extends AbstractController {
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
     * @param MySchedule $schedule
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveSchedule(MySchedule $schedule): void
    {
        $this->app->repositories->myScheduleRepository->saveSchedule($schedule);
    }

    /**
     * Will return all not deleted schedules
     *
     * @return MySchedule[]
     */
    public function getAllNotDeletedSchedules(): array
    {
        return $this->app->repositories->myScheduleRepository->getAllNotDeletedSchedules();
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
        return $this->app->repositories->myScheduleRepository->findOneScheduleById($id);
    }

}
