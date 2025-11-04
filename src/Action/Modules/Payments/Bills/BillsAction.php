<?php


namespace App\Action\Modules\Payments\Bills;


use App\Annotation\System\ModuleAnnotation;
use App\Entity\Modules\Payments\MyPaymentsBills;
use App\Entity\Modules\Payments\MyPaymentsBillsItems as BillItem;
use App\Repository\Modules\Payments\MyPaymentsBillsRepository;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/payment/bills", name: "module.payment.bills.")]
#[ModuleAnnotation(values: ["name" => ModulesService::MODULE_NAME_PAYMENTS])]
class BillsAction extends AbstractController {


    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MyPaymentsBillsRepository $billsRepository
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
        $bills       = $this->billsRepository->getAllNotDeleted();
        $entriesData = [];
        foreach ($bills as $bill) {
            $elements = [];

            $items = $bill->getItem()->getValues();
            usort($items, fn(BillItem $curr,BillItem $next) => $curr->getDate()->getTimestamp() < $next->getDate()->getTimestamp());
            foreach ($items as $item) {
                if ($item->isDeleted()) {
                    continue;
                }

                $elements[] = [
                    'id'     => $item->getId(),
                    'amount' => $item->getAmount(),
                    'name'   => $item->getName(),
                    'date'   => $item->getDate()?->format('Y-m-d'),
                ];
            }

            $entriesData[] = [
                'id'            => $bill->getId(),
                'name'          => $bill->getName(),
                'startDate'     => $bill->getStartDate()?->format("Y-m-d"),
                'endDate'       => $bill->getEndDate()?->format("Y-m-d"),
                'information'   => $bill->getInformation(),
                'plannedAmount' => $bill->getPlannedAmount(),
                'elements'      => $elements,
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyPaymentsBills $bill
     * @param Request         $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyPaymentsBills $bill, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $bill)->toJsonResponse();
    }

    /**
     * @param MyPaymentsBills $bill
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyPaymentsBills $bill): JsonResponse
    {
        $bill->setDeleted(true);
        $this->em->persist($bill);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request              $request
     * @param MyPaymentsBills|null $bill
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyPaymentsBills $bill = null): BaseResponse
    {
        if (!$bill) {
            $bill = new MyPaymentsBills();
        }

        $dataArray       = RequestService::tryFromJsonBody($request);
        $startDateString = ArrayHandler::get($dataArray, 'startDate', allowEmpty: false);
        $endDateString   = ArrayHandler::get($dataArray, 'endDate', allowEmpty: false);
        $name            = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $information     = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $plannedAmount   = ArrayHandler::get($dataArray, 'plannedAmount', allowEmpty: false);

        $bill->setStartDate(new DateTime($startDateString));
        $bill->setEndDate(new DateTime($endDateString));
        $bill->setInformation($information);
        $bill->setPlannedAmount((int)$plannedAmount);
        $bill->setName($name);

        $this->em->persist($bill);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}