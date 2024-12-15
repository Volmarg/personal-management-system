<?php

namespace App\Action\Modules\Reports\Payments;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Reports\ReportsController;
use App\Response\Base\BaseResponse;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/reports/payments/total-per-month", name: "module.reports.payments.total-per-month")]
#[ModuleAnnotation(values: ["name" => ModulesController::MENU_NODE_MODULE_NAME_REPORTS])]
class TotalPerMonth extends AbstractController {

    public function __construct(
        private readonly ReportsController   $reportsController,
        private readonly TranslatorInterface $translator
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
        $paymentsPerMonth = $this->reportsController->buildPaymentsSummariesForMonthsAndYears();
        $billsGroupName   = $this->translator->trans("module.reports.payments.byTypePerMonth.withBills");
        $noBillsGroupName = $this->translator->trans("module.reports.payments.byTypePerMonth.withoutBills");

        $entriesData = [
            $noBillsGroupName => [],
            $billsGroupName   => [],
        ];

        foreach ($paymentsPerMonth as $monthPayments) {
            $entriesData[$noBillsGroupName][] = [
                'value' => $monthPayments['moneyWithoutBills'],
                'label' => $monthPayments['yearAndMonth'],
            ];

            $entriesData[$billsGroupName][] = [
                'value' => $monthPayments['money'],
                'label' => $monthPayments['yearAndMonth'],
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

}