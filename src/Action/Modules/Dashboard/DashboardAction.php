<?php

namespace App\Action\Modules\Dashboard;

use App\Controller\Modules\Goals\GoalsListController;
use App\Controller\Modules\Goals\GoalsPaymentsController;
use App\Controller\Modules\Issues\MyIssuesController;
use App\Controller\Modules\Todo\MyTodoController;
use App\Entity\Modules\Schedules\MySchedule;
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
        private readonly GoalsPaymentsController $goalsPaymentsController,
        private readonly MyTodoController        $todoController,
        private readonly GoalsListController     $goalsListController,
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

        $allPayments = $this->goalsPaymentsController->getAllNotDeleted();
        foreach ($allPayments as $payment) {
            $entriesData['goalPayments'][] = $payment->asFrontendData();
        }

        $goals                       = $this->goalsListController->getGoals();
        $entriesData['goalProgress'] = $this->todoController->buildFrontDataArray($goals);

        $allOngoingIssues      = $this->myIssuesController->findAllNotDeletedAndNotResolved();
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