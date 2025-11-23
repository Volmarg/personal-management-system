<?php

namespace App\Action\Modules\Reports;

use App\Attribute\ModuleAttribute;
use App\Repository\Modules\Payments\MyPaymentsIncomeRepository;
use App\Repository\Modules\Reports\ReportsRepository;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/reports/savings", name: "module.reports.savings.")]
#[ModuleAttribute(values: ["name" => ModulesService::MENU_NODE_MODULE_NAME_REPORTS])]
class SavingsAction extends AbstractController {

    public function __construct(
        private readonly MyPaymentsIncomeRepository $paymentsIncomeRepository,
        private readonly ReportsRepository $reportsRepository
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
        $expensesPerMonth = $this->reportsRepository->buildPaymentsSummariesForMonthsAndYears();
        $incomesPerMonth  = $this->paymentsIncomeRepository->getAllNotDeletedSummedByYearAndMonth();
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