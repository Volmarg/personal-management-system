<?php

namespace App\Action\Modules\Reports;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Payments\MyPaymentsIncomeController;
use App\Controller\Modules\Reports\ReportsController;
use App\Response\Base\BaseResponse;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/reports/savings", name: "module.reports.savings.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MENU_NODE_MODULE_NAME_REPORTS])]
class SavingsAction extends AbstractController {

    public function __construct(
        private readonly MyPaymentsIncomeController $paymentsIncomeController,
        private readonly ReportsController          $reportsController
    ) {
    }

    /**
     * Contains some legacy code
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $expensesPerMonth = $this->reportsController->buildPaymentsSummariesForMonthsAndYears();
        $incomesPerMonth  = $this->paymentsIncomeController->getAllNotDeletedSummedByYearAndMonth();
        $entriesData      = [];
        foreach ($expensesPerMonth as $monthExpenses) {
            $date   = $monthExpenses['yearAndMonth'];
            $saving = 0;

            foreach ($incomesPerMonth as $yearAndMonth => $incomeAmount) {
                if ($yearAndMonth === $date) {
                    $moneySpent = round((float)$monthExpenses['money'], 2);
                    $saving     = $incomeAmount - $moneySpent;
                    $saving     = ($saving < 0 ? 0 : $saving);
                    break;
                }
            }

            $entriesData[] = [
                'value' => $saving,
                'label' => $date,
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

}