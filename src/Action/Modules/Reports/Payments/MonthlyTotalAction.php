<?php

namespace App\Action\Modules\Reports\Payments;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Repository\Modules\Reports\ReportsRepository;
use App\Response\Base\BaseResponse;
use App\Services\Chart\LinearChartDataHandler;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/reports/payments/total-per-month", name: "module.reports.payments.total-per-month")]
#[ModuleAnnotation(values: ["name" => ModulesController::MENU_NODE_MODULE_NAME_REPORTS])]
class MonthlyTotalAction extends AbstractController {

    public function __construct(
        private readonly ReportsRepository   $reportsRepository,
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
        $paymentsPerMonth = $this->reportsRepository->buildPaymentsSummariesForMonthsAndYears();
        $billsGroupName   = $this->translator->trans("module.reports.payments.byTypePerMonth.withBills");
        $noBillsGroupName = $this->translator->trans("module.reports.payments.byTypePerMonth.withoutBills");

        $entriesData = [
            $noBillsGroupName => [],
            $billsGroupName   => [],
        ];

        $lowestDate        = null;
        $highestDate       = null;
        $existingTypeDates = [];

        foreach ($paymentsPerMonth as $idx => $monthPayments) {
            $yearAndMonth = $monthPayments['yearAndMonth'];
            if (is_null($lowestDate)) {
                $lowestDate = new DateTimeImmutable($yearAndMonth);
            }

            if ($idx === count($paymentsPerMonth) - 1) {
                $highestDate = new DateTimeImmutable($yearAndMonth);
            }

            $entriesData[$noBillsGroupName][] = [
                'value' => $monthPayments['moneyWithoutBills'],
                'label' => $yearAndMonth,
            ];

            $entriesData[$billsGroupName][] = [
                'value' => $monthPayments['money'],
                'label' => $yearAndMonth,
            ];
        }

        $entriesData = LinearChartDataHandler::fillMissingMonths($lowestDate, $highestDate, $entriesData, $existingTypeDates);
        foreach ($entriesData as &$dataChunks) {
            usort($dataChunks, fn($a, $b) => strtotime($a['label']) -  strtotime($b['label']));
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

}