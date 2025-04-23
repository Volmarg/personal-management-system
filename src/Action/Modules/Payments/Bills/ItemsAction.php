<?php


namespace App\Action\Modules\Payments\Bills;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Payments\MyPaymentsBills;
use App\Entity\Modules\Payments\MyPaymentsBillsItems;
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

#[Route("/module/payment/bills/items", name: "module.payment.bills.items.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_PAYMENTS])]
class ItemsAction extends AbstractController {


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
     * @param MyPaymentsBillsItems $item
     * @param Request              $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyPaymentsBillsItems $item, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $item)->toJsonResponse();
    }

    /**
     * @param MyPaymentsBillsItems $item
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyPaymentsBillsItems $item): JsonResponse
    {
        $item->setDeleted(true);
        $this->em->persist($item);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request                   $request
     * @param MyPaymentsBillsItems|null $item
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyPaymentsBillsItems $item = null): BaseResponse
    {
        if (!$item) {
            $item = new MyPaymentsBillsItems();
        }

        $dataArray  = RequestService::tryFromJsonBody($request);
        $dateString = ArrayHandler::get($dataArray, 'date', allowEmpty: false);
        $amount     = ArrayHandler::get($dataArray, 'amount', allowEmpty: false);
        $billId     = ArrayHandler::get($dataArray, 'billId', allowEmpty: false);
        $name       = ArrayHandler::get($dataArray, 'name', allowEmpty: false);

        $bill = $this->em->find(MyPaymentsBills::class, $billId);
        if (is_null($bill)) {
            throw new Exception("No bill was found for id: {$billId}");
        }

        $item->setDate(new DateTime($dateString));
        $item->setName($name);
        $item->setAmount((int)$amount);
        $item->setBill($bill);

        $this->em->persist($item);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}