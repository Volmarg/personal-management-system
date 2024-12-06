<?php

namespace App\Action\Modules\Payments\Settings;

use App\Controller\Modules\Payments\MyRecurringPaymentsMonthlyController;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use App\Response\Base\BaseResponse;
use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/payment/setting/recurring-payment", name: "module.payment.monthly.setting.recurring_payment")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_PAYMENTS])]
class RecurringPaymentAction extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface               $em,
        private readonly MyRecurringPaymentsMonthlyController $recurringPaymentsMonthlyController,
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
        $allRecurringPayments = $this->recurringPaymentsMonthlyController->getAllNotDeleted();
        $entriesData = [];
        foreach ($allRecurringPayments as $recurringPayment) {
            $entriesData[] = [
                'id'          => $recurringPayment->getId(),
                'dayOfMonth'  => $recurringPayment->getDayOfMonth() ?? 1,
                'amount'      => $recurringPayment->getMoney() ?? 0,
                'description' => $recurringPayment->getDescription(),
                'typeId'      => $recurringPayment->getType()?->getId(),
                'typeName'    => $recurringPayment->getType()?->getValue(), // that's correct
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyRecurringPaymentMonthly $payment
     * @param Request                   $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyRecurringPaymentMonthly $payment, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $payment)->toJsonResponse();
    }

    /**
     * @param MyRecurringPaymentMonthly $payment
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyRecurringPaymentMonthly $payment): JsonResponse
    {
        $payment->setDeleted(true);
        $this->em->persist($payment);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request                        $request
     * @param MyRecurringPaymentMonthly|null $payment
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyRecurringPaymentMonthly $payment = null): BaseResponse
    {
        if (!$payment) {
            $payment = new MyRecurringPaymentMonthly();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $dayOfMonth  = ArrayHandler::get($dataArray, 'dayOfMonth');
        $typeId      = ArrayHandler::get($dataArray, 'typeId');
        $description = ArrayHandler::get($dataArray, 'description');
        $amount      = ArrayHandler::get($dataArray, 'amount');

        $type = $this->em->getRepository(MyPaymentsSettings::class)->findOneById($typeId);
        if (is_null($type)) {
            throw new Exception("No type found for id {$typeId}");
        }

        $payment->setDayOfMonth($dayOfMonth);
        $payment->setType($type);
        $payment->setDescription($description);
        $payment->setMoney($amount);

        $this->em->persist($payment);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}