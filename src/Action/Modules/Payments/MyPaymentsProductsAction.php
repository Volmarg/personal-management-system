<?php


namespace App\Action\Modules\Payments;


use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Payments\MyPaymentsProduct;
use App\Repository\Modules\Payments\MyPaymentsProductRepository;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
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

#[Route("/module/payment/product-prices", name: "module.product-prices.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_PAYMENTS])]
class MyPaymentsProductsAction extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface       $em,
        private readonly MyPaymentsProductRepository  $paymentsProductRepository,
        private readonly MyPaymentsSettingsRepository $myPaymentsSettingsRepository
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
        $entriesData = [];
        $products    = $this->paymentsProductRepository->getAllNotDeleted();
        $multiplier  = $this->myPaymentsSettingsRepository->fetchCurrencyMultiplier();
        foreach ($products as $product) {
            $entriesData[] = [
                'id'                => $product->getId(),
                'name'              => $product->getName(),
                'market'            => $product->getMarket(),
                'products'          => $product->getProducts(),
                'information'       => $product->getInformation(),
                'rejected'          => $product->getRejected() ?? false,
                'price'             => $product->getPrice(),
                'homeCurrencyPrice' => (!$product->getPrice() || !$multiplier ? null : round($product->getPrice() * $multiplier, 2)),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyPaymentsProduct $product
     * @param Request           $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyPaymentsProduct $product, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $product)->toJsonResponse();
    }

    /**
     * @param MyPaymentsProduct $product
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyPaymentsProduct $product): JsonResponse
    {
        $product->setDeleted(true);
        $this->em->persist($product);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request                $request
     * @param MyPaymentsProduct|null $product
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyPaymentsProduct $product = null): BaseResponse
    {
        if (!$product) {
            $product = new MyPaymentsProduct();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $name        = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $market      = ArrayHandler::get($dataArray, 'market');
        $products    = ArrayHandler::get($dataArray, 'products');
        $information = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $rejected    = ArrayHandler::get($dataArray, 'rejected');
        $price       = ArrayHandler::get($dataArray, 'price', allowEmpty: false);

        $product->setName($name);
        $product->setMarket($market);
        $product->setProducts($products);
        $product->setInformation($information);
        $product->setRejected($rejected);
        $product->setPrice($price);

        $this->em->persist($product);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}