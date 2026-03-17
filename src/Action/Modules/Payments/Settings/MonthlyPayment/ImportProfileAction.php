<?php

namespace App\Action\Modules\Payments\Settings\MonthlyPayment;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Payments\Monthly\Import\ImportProfile;
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

#[Route("/module/payment/setting/payment-monthly-import-profile", name: "module.payment.monthly.setting.payment_monthly.import_profile")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_PAYMENTS])]
class ImportProfileAction extends AbstractController {

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
        $profiles = $this->em->getRepository(ImportProfile::class)->findAll();
        $entriesData = [];
        foreach ($profiles as $profile) {
            $entriesData[] = [
                'id'               => $profile->getId(),
                'name'             => $profile->getName(),
                'currencyField'    => $profile->getCurrencyField(),
                'descriptionField' => $profile->getDescriptionField(),
                'moneyField'       => $profile->getMoneyField(),
                'dateField'        => $profile->getDateField(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param ImportProfile $rule
     * @param Request       $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(ImportProfile $rule, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $rule)->toJsonResponse();
    }

    /**
     * @param ImportProfile $profile
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(ImportProfile $profile): JsonResponse
    {
        $this->em->remove($profile);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request            $request
     * @param ImportProfile|null $importProfile
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?ImportProfile $importProfile = null): BaseResponse
    {
        $importProfile ??= new ImportProfile();

        $dataArray        = RequestService::tryFromJsonBody($request);
        $descriptionField = ArrayHandler::get($dataArray, 'descriptionField', allowEmpty: false);
        $currencyField    = ArrayHandler::get($dataArray, 'currencyField', allowEmpty: false);
        $moneyField       = ArrayHandler::get($dataArray, 'moneyField', allowEmpty: false);
        $dateField        = ArrayHandler::get($dataArray, 'dateField', allowEmpty: false);
        $name             = ArrayHandler::get($dataArray, 'name', allowEmpty: false);

        $importProfile->setName($name);
        $importProfile->setCurrencyField($currencyField);
        $importProfile->setDescriptionField($descriptionField);
        $importProfile->setMoneyField($moneyField);
        $importProfile->setDateField($dateField);

        $this->em->persist($importProfile);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}