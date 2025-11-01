<?php

namespace App\Action\Modules\Payments\Settings;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/payment/setting/currency-multiplier", name: "module.payment.monthly.setting.currency_multiplier")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_PAYMENTS])]
class CurrencyMultiplierAction extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface       $em,
        private readonly MyPaymentsSettingsRepository $myPaymentsSettingsRepository
    ) {
    }

    /**
     * @return JsonResponse
     */
    #[Route("", name: "get", methods: [Request::METHOD_GET])]
    public function getOne(): JsonResponse
    {
        $multiplier = $this->myPaymentsSettingsRepository->fetchCurrencyMultiplier();
        $response   = BaseResponse::buildOkResponse();
        $response->setSingleRecordData([
            'value' => $multiplier ?? 1,
        ]);

        return $response->toJsonResponse();
    }

    /**
     * Currency multiplier should exist in the system, if it doesn't then create new one.
     * Not playing around with new / update, there is only one such entry in whole project.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(Request $request): JsonResponse
    {
        $setting = $this->em->getRepository(MyPaymentsSettings::class)->findOneBy(['name' => MyPaymentsSettings::TYPE_CURRENCY_MULTIPLIER]);
        if (!$setting) {
            $setting = new MyPaymentsSettings();
            $setting->setName(MyPaymentsSettings::TYPE_CURRENCY_MULTIPLIER);
        }

        $dataArray = RequestService::tryFromJsonBody($request);
        $value     = ArrayHandler::get($dataArray, 'currencyMultiplier', allowEmpty: false);

        $setting->setValue($value);
        $setting->setDeleted(false);

        $this->em->persist($setting);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

}