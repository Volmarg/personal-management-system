<?php


namespace App\Action\Modules\Payments;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Payments\MyPaymentsOwed;
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

#[Route("/module/payment/owed", name: "module.payment.owed.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_PAYMENTS])]
class MyPaymentsOwedAction extends AbstractController
{

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
        $entries = $this->em->getRepository(MyPaymentsOwed::class)->findBy(['deleted' => false], ["date" => "DESC"]);

        $entriesData = [];
        foreach ($entries as $owedEntry) {
            $entriesData[] = [
                'id'          => $owedEntry->getId(),
                'owedByMe'    => $owedEntry->getOwedByMe() ?? false,
                'target'      => $owedEntry->getTarget(),
                'amount'      => $owedEntry->getAmount(),
                'information' => $owedEntry->getInformation(),
                'date'        => $owedEntry->getDate()?->format("Y-m-d"),
                'currency'    => $owedEntry->getCurrency(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyPaymentsOwed $owedEntry
     * @param Request        $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyPaymentsOwed $owedEntry, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $owedEntry)->toJsonResponse();
    }

    /**
     * @param MyPaymentsOwed $owedEntry
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyPaymentsOwed $owedEntry): JsonResponse
    {
        $owedEntry->setDeleted(true);
        $this->em->persist($owedEntry);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request             $request
     * @param MyPaymentsOwed|null $owedEntry
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyPaymentsOwed $owedEntry = null): BaseResponse
    {
        if (!$owedEntry) {
            $owedEntry = new MyPaymentsOwed();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $target      = ArrayHandler::get($dataArray, 'target', allowEmpty: false);
        $information = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $dateString  = ArrayHandler::get($dataArray, 'date', allowEmpty: false);
        $amount      = ArrayHandler::get($dataArray, 'amount', allowEmpty: false);
        $currency    = ArrayHandler::get($dataArray, 'currency', allowEmpty: false);
        $owedByMe    = ArrayHandler::get($dataArray, 'owedByMe');

        $owedEntry->setDate(new DateTime($dateString));
        $owedEntry->setTarget($target);
        $owedEntry->setInformation($information);
        $owedEntry->setAmount((int)$amount);
        $owedEntry->setCurrency($currency);
        $owedEntry->setOwedByMe((bool)$owedByMe);

        $this->em->persist($owedEntry);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}