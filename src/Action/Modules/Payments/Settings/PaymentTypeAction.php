<?php

namespace App\Action\Modules\Payments\Settings;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/payment/setting/payment-type", name: "module.payment.monthly.setting.payment_type")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_PAYMENTS])]
class PaymentTypeAction extends AbstractController {

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
        $settings = $this->em->getRepository(MyPaymentsSettings::class)->getAllPaymentsTypes();
        $entriesData = [];
        foreach ($settings as $setting) {
            $entriesData[] = [
                'id'    => $setting->getId(),
                'name'  => $setting->getValue(), // that's correct
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyPaymentsSettings $setting
     * @param Request          $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyPaymentsSettings $setting, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $setting)->toJsonResponse();
    }

    /**
     * @param MyPaymentsSettings $setting
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyPaymentsSettings $setting): JsonResponse
    {
        $setting->setDeleted(true);
        $this->em->persist($setting);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request                 $request
     * @param MyPaymentsSettings|null $setting
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyPaymentsSettings $setting = null): BaseResponse
    {
        if (!$setting) {
            $setting = new MyPaymentsSettings();
            $setting->setName(MyPaymentsSettings::TYPE_PAYMENT_TYPE);
        }

        $dataArray = RequestService::tryFromJsonBody($request);
        $name      = ArrayHandler::get($dataArray, 'name', allowEmpty: false);

        $setting->setValue($name);

        $this->em->persist($setting);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}