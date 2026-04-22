<?php

namespace App\Action\Modules\Payments\Settings\MonthlyPayment;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Payments\Monthly\Import\ImportFilterRule;
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

#[Route("/module/payment/setting/payment-monthly-import-filter-rules", name: "module.payment.monthly.setting.payment_monthly.import_filter_rules")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_PAYMENTS])]
class ImportFilterRulesAction extends AbstractController {

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
        $rules = $this->em->getRepository(ImportFilterRule::class)->findAll();
        $entriesData = [];
        foreach ($rules as $rule) {
            $entriesData[] = [
                'id'          => $rule->getId(),
                'fieldName'   => $rule->getFieldName(),
                'rule'        => $rule->getRule(),
                'type'        => $rule->getType(),
                'description' => $rule->getDescription(),
                'profile'     => [
                    'id'   => $rule->getImportProfile()?->getId(),
                    'name' => $rule->getImportProfile()?->getName(),
                ],
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param ImportFilterRule $rule
     * @param Request            $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(ImportFilterRule $rule, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $rule)->toJsonResponse();
    }

    /**
     * @param ImportFilterRule $rule
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(ImportFilterRule $rule): JsonResponse
    {
        $this->em->remove($rule);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request               $request
     * @param ImportFilterRule|null $filterRule
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?ImportFilterRule $filterRule = null): BaseResponse
    {
        $filterRule ??= new ImportFilterRule();

        $dataArray   = RequestService::tryFromJsonBody($request);
        $fieldName   = ArrayHandler::get($dataArray, 'fieldName', allowEmpty: false);
        $rule        = ArrayHandler::get($dataArray, 'rule', allowEmpty: false);
        $type        = ArrayHandler::get($dataArray, 'type', allowEmpty: false);
        $description = ArrayHandler::get($dataArray, 'description');
        $profileId   = ArrayHandler::get($dataArray, 'profileId');

        $profile = is_null($profileId) ? null : $this->em->find(ImportProfile::class, $profileId);

        $filterRule->setRule($rule);
        $filterRule->setType($type);
        $filterRule->setFieldName($fieldName);
        $filterRule->setDescription($description);
        $filterRule->setImportProfile($profile);

        $this->em->persist($filterRule);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}