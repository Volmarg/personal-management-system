<?php


namespace App\Action\Modules\Payments;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Payments\MyPaymentsIncome;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Code ensures that currency name is unique upon saving
 */
#[Route("/module/payment/income", name: "module.payment.income.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_PAYMENTS])]
class MyPaymentsIncomeAction extends AbstractController {


    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("", name: "new", methods: [Request::METHOD_POST])]
    public function new(Request $request): JsonResponse
    {
        return $this->createOrUpdate($request)->toJsonResponse();
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $icnomes     = $this->em->getRepository(MyPaymentsIncome::class)->getAllNotDeleted();
        $entriesData = [];
        foreach ($icnomes as $income) {
            $entriesData[] = [
                'id'          => $income->getId(),
                'date'        => $income->getDate()?->format('Y-m-d'),
                'amount'      => $income->getAmount(),
                'information' => $income->getInformation(),
                'currency'    => $income->getCurrency(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyPaymentsIncome $income
     * @param Request          $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyPaymentsIncome $income, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $income)->toJsonResponse();
    }

    /**
     * @param MyPaymentsIncome $income
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyPaymentsIncome $income): JsonResponse
    {
        $income->setDeleted(true);
        $this->em->persist($income);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request               $request
     * @param MyPaymentsIncome|null $income
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyPaymentsIncome $income = null): BaseResponse
    {
        if (!$income) {
            $income = new MyPaymentsIncome();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $dateString  = ArrayHandler::get($dataArray, 'date', allowEmpty: false);
        $amount      = ArrayHandler::get($dataArray, 'amount', allowEmpty: false);
        $information = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $currency    = ArrayHandler::get($dataArray, 'currency', allowEmpty: false);

        $income->setDate(new DateTime($dateString));
        $income->setInformation($information);
        $income->setAmount((int)$amount);
        $income->setCurrency($currency);

        $this->em->persist($income);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}