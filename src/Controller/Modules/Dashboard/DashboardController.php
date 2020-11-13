<?php

namespace App\Controller\Modules\Dashboard;

use App\Controller\Core\Application;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Todo\MyTodo;
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
     * @return array
     */
    public function getIncomingSchedules() {
        return $this->app->repositories->myScheduleRepository->getIncomingSchedulesInDays(self::SCHEDULES_DEFAULT_DAYS_INTERVAL);
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
