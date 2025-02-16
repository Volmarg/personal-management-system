<?php

namespace App\Action\Modules\Dashboard;

use App\Controller\Modules\Issues\MyIssuesController;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Todo\MyTodoController;
use App\Entity\Modules\Goals\MyGoalsPayments;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Schedules\MySchedule;
use App\Entity\Modules\Todo\MyTodo;
use App\Response\Base\BaseResponse;
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
        private readonly EntityManagerInterface  $em
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
        // todo: fill the data only if widget is set to visible (in config)
        $entriesData = [
            'goalPayments' => [],
            'goalProgress' => [],
            'issues'       => [],
            'schedules'    => [],
        ];

        $allPayments = $this->em->getRepository(MyGoalsPayments::class)->getGoalsPaymentsForDashboard();
        foreach ($allPayments as $payment) {
            $entriesData['goalPayments'][] = $payment->asFrontendData();
        }

        $goals                       = $this->em->getRepository(MyTodo::class)->getEntitiesForModuleName(ModulesController::MODULE_NAME_GOALS, true);
        $entriesData['goalProgress'] = $this->todoController->buildFrontDataArray($goals);

        $allOngoingIssues      = $this->em->getRepository(MyIssue::class)->getPendingIssuesForDashboard();
        $entriesData['issues'] = $this->myIssuesController->getIssuesData($allOngoingIssues);


        $schedules = $this->em->getRepository(MySchedule::class)->findForDashboard();
        foreach ($schedules as $schedule) {
            $entriesData['schedules'][] = $schedule->asFrontendData();
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

}