<?php

namespace App\Action\Modules\Dashboard;

use App\Controller\Modules\Issues\MyIssuesController;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Todo\MyTodoController;
use App\Entity\Modules\Goals\MyGoalsPayments;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Schedules\MySchedule;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\Setting;
use App\Response\Base\BaseResponse;
use App\Services\Settings\SettingsLoader;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/dashboard", name: "module.dashboard.")]
class DashboardAction extends AbstractController {

    public function __construct(
        private readonly MyTodoController        $todoController,
        private readonly MyIssuesController      $myIssuesController,
        private readonly EntityManagerInterface  $em,
        private readonly SettingsLoader          $settingsLoader
    ) {
    }

    /**
     * Performance on this function is poor, but it should suffice, if needed,
     * fetch only entries visible for dashboard (keep in mind system lock)
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $entriesData = [
            Setting::DASHBOARD_WIDGET_GOAL_PROGRESS => [],
            Setting::DASHBOARD_WIDGET_GOAL_PAYMENTS => [],
            Setting::DASHBOARD_WIDGET_ISSUES        => [],
            Setting::DASHBOARD_WIDGET_SCHEDULES     => [],
        ];

        if ($this->settingsLoader->isDashboardWidgetVisible(Setting::DASHBOARD_WIDGET_GOAL_PAYMENTS)) {
            $allPayments = $this->em->getRepository(MyGoalsPayments::class)->getGoalsPaymentsForDashboard();
            foreach ($allPayments as $payment) {
                $entriesData[Setting::DASHBOARD_WIDGET_GOAL_PAYMENTS][] = $payment->asFrontendData();
            }
        }

        if ($this->settingsLoader->isDashboardWidgetVisible(Setting::DASHBOARD_WIDGET_GOAL_PROGRESS)) {
            $goals = $this->em->getRepository(MyTodo::class)->getEntitiesForModuleName(ModulesController::MODULE_NAME_GOALS, true);
            $entriesData[Setting::DASHBOARD_WIDGET_GOAL_PROGRESS] = $this->todoController->buildFrontDataArray($goals);
        }

        if ($this->settingsLoader->isDashboardWidgetVisible(Setting::DASHBOARD_WIDGET_ISSUES)) {
            $allOngoingIssues = $this->em->getRepository(MyIssue::class)->getPendingIssuesForDashboard();
            $entriesData[Setting::DASHBOARD_WIDGET_ISSUES] = $this->myIssuesController->getIssuesData($allOngoingIssues);
        }

        if ($this->settingsLoader->isDashboardWidgetVisible(Setting::DASHBOARD_WIDGET_SCHEDULES)) {
            $schedules = $this->em->getRepository(MySchedule::class)->findForDashboard();
            foreach ($schedules as $schedule) {
                $entriesData[Setting::DASHBOARD_WIDGET_SCHEDULES][] = $schedule->asFrontendData();
            }
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

}