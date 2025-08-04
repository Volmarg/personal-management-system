<?php

namespace App\Action\Modules\Reports\Payments;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Repository\Modules\Reports\ReportsRepository;
use App\Response\Base\BaseResponse;
use App\Services\Chart\LinearChartDataHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/reports/payments/total-per-type", name: "module.reports.payments.total-per-type")]
#[ModuleAnnotation(values: ["name" => ModulesController::MENU_NODE_MODULE_NAME_REPORTS])]
class MonthlyPerTypeAction extends AbstractController {

    public function __construct(
        private readonly ReportsRepository $reportsRepository,
    ) {
    }

    /**
     * Contains some legacy code.
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $amountsForTypes   = $this->reportsRepository->fetchTotalPaymentsAmountForTypes();
        $entriesData       = [];
        $lowestDate        = null;
        $highestDate       = null;
        $existingTypeDates = [];

        foreach($amountsForTypes as $idx => $amountsForType){
            $type   = $amountsForType['type'];
            $amount = $amountsForType['amount'];
            $date   = $amountsForType['date'];

            $existingTypeDates[$type][] = $date;

            if (is_null($lowestDate)) {
                $lowestDate = new \DateTimeImmutable($date);
            }

            if ($idx === count($amountsForTypes) - 1) {
                $highestDate = new \DateTimeImmutable($date);
            }

            if (!array_key_exists($type, $entriesData)) {
                $entriesData[$type] = [];
            }

            $entriesData[$type][] = [
              'value' => $amount,
              'label' => $date,
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