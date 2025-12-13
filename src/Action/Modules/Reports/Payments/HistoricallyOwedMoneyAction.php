<?php

namespace App\Action\Modules\Reports\Payments;

use App\Attribute\ModuleAttribute;
use App\Repository\Modules\Reports\ReportsRepository;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/reports/money-owed/historical", name: "module.reports.money_owed.historical")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_REPORTS])]
class HistoricallyOwedMoneyAction extends AbstractController {

    public function __construct(
        private readonly ReportsRepository $reportsRepository,
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
        $historicalEntries = $this->reportsRepository->fetchHistoricalOwedMoney();
        $entriesData       = [];

        foreach ($historicalEntries as $owedMoney) {
            $entriesData[] = [
                'target'      => $owedMoney->getTarget(),
                'amount'      => $owedMoney->getAmount(),
                'information' => $owedMoney->getInformation(),
                'date'        => $owedMoney->getDate()?->format("Y-m-d"),
                'currency'    => $owedMoney->getCurrency(),
                'owedByMe'    => $owedMoney->getOwedByMe() ?? false,
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

}