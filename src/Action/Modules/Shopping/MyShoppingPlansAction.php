<?php

namespace App\Action\Modules\Shopping;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Shopping\MyShoppingPlans;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/shopping/plans", name: "module.shopping.plans.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_SHOPPING])]
class MyShoppingPlansAction extends AbstractController
{

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
        /** @var MyShoppingPlans[] $allPlans */
        $allPlans = $this->em->getRepository(MyShoppingPlans::class)->findBy(['deleted' => 0]);

        $entriesData = [];
        foreach ($allPlans as $plan) {
            $entriesData[] = [
                'id'          => $plan->getId(),
                'information' => $plan->getInformation(),
                'example'     => $plan->getExample(),
                'name'        => $plan->getName(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyShoppingPlans $shoppingPlan
     * @param Request         $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyShoppingPlans $shoppingPlan, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $shoppingPlan)->toJsonResponse();
    }

    /**
     * @param MyShoppingPlans $shoppingPlan
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyShoppingPlans $shoppingPlan): JsonResponse
    {
        $shoppingPlan->setDeleted(true);
        $this->em->persist($shoppingPlan);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request              $request
     * @param MyShoppingPlans|null $shoppingPlan
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyShoppingPlans $shoppingPlan = null): BaseResponse
    {
        $isNew = is_null($shoppingPlan);
        if ($isNew) {
            $shoppingPlan = new MyShoppingPlans();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $name        = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $information = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $example     = ArrayHandler::get($dataArray, 'example');

        $shoppingPlan->setName($name);
        $shoppingPlan->setInformation($information);
        $shoppingPlan->setExample($example);

        $this->em->persist($shoppingPlan);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}