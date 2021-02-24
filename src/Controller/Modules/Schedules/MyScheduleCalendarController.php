<?php

namespace App\Controller\Modules\Schedules;

use App\Controller\Core\Application;
use App\DTO\Modules\Schedules\ScheduleCalendarDTO;
use App\Entity\Modules\Schedules\MyScheduleCalendar;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyScheduleCalendarController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Will fetch all non deleted calendars in form of array
     *
     * @return ScheduleCalendarDTO[]
     * @throws Exception
     */
    public function fetchAllNonDeletedCalendarsData(): array
    {
        return $this->app->repositories->myScheduleCalendarRepository->fetchAllNonDeletedCalendarsData();
    }

    /**
     * Will return calendar entity for provided id or null if nothing is found
     *
     * @param string $id
     * @return MyScheduleCalendar|null
     */
    public function findCalendarById(string $id): ?MyScheduleCalendar
    {
        return $this->app->repositories->myScheduleCalendarRepository->findCalendarById($id);
    }
}
