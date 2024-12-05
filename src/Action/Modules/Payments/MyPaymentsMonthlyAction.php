<?php


namespace App\Action\Modules\Payments;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Payments\MyPaymentsMonthlyController;
use App\Entity\Modules\Payments\MyPaymentsMonthly;
use App\Entity\Modules\Payments\MyPaymentsSettings;
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

#[Route("/module/payment/monthly", name: "module.payment.monthly.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_PAYMENTS])]
class MyPaymentsMonthlyAction extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface $em,
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
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $allPayments = $this->em->getRepository(MyPaymentsMonthly::class)->findBy(['deleted' => 0]);
        $entriesData = [];
        foreach ($allPayments as $payment) {
            $entriesData[] = [
                'id'          => $payment->getId(),
                'date'        => $payment->getDate()->format('Y-m-d'),
                'money'       => $payment->getMoney() ?? 0,
                'description' => $payment->getDescription() ?? '',
                'typeName'    => $payment->getType()?->getValue() ?? '',
                'typeId'      => $payment->getType()?->getId(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyPaymentsMonthly $payment
     * @param Request           $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyPaymentsMonthly $payment, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $payment)->toJsonResponse();
    }

    /**
     * @param MyPaymentsMonthly $payment
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyPaymentsMonthly $payment): JsonResponse
    {
        $payment->setDeleted(true);
        $this->em->persist($payment);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request                $request
     * @param MyPaymentsMonthly|null $payment
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyPaymentsMonthly $payment = null): BaseResponse
    {
        if (!$payment) {
            $payment = new MyPaymentsMonthly();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $dateString  = ArrayHandler::get($dataArray, 'date');
        $description = ArrayHandler::get($dataArray, 'description');
        $money       = ArrayHandler::get($dataArray, 'money');
        $typeId      = ArrayHandler::get($dataArray, 'typeId');

        $type = $this->em->getRepository(MyPaymentsSettings::class)->findPaymentType($typeId);
        if (!$type) {
            throw new Exception("No payment type setting found for id: {$type}");
        }

        $payment->setDate(new DateTime($dateString));
        $payment->setDescription($description);
        $payment->setMoney((int)$money);
        $payment->setType($type);

        $this->em->persist($payment);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}