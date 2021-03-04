<?php

namespace App\Controller\Modules\Dashboard;

use App\Controller\Core\Application;
use App\Controller\Modules\ModulesController;
use App\DTO\Modules\Schedules\IncomingScheduleDTO;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Todo\MyTodo;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController {

    const SCHEDULES_DEFAULT_DAYS_INTERVAL = 60;

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * @param int|null $limit
     * @return IncomingScheduleDTO[]
     * @throws Exception
     */
    public function getIncomingSchedulesInformation(?int $limit = null) {
        return  $this->app->repositories->myScheduleRepository->getIncomingSchedulesInformationInDays(self::SCHEDULES_DEFAULT_DAYS_INTERVAL, $limit);
    }

    /**
     * @return MyTodo[]
     */
    public function getGoalsTodoForWidget(){
        return $this->app->repositories->myTodoRepository->getEntitiesForModuleName(ModulesController::MODULE_NAME_GOALS, true);
    }

    public function getGoalsPayments(){
        return $this->app->repositories->myGoalsPaymentsRepository->getGoalsPaymentsForDashboard();
    }

    /**
     * @return MyIssue[]
     */
    public function getPendingIssues(): array
    {
        return $this->app->repositories->myIssueRepository->getPendingIssuesForDashboard();
    }

}
