<?php


namespace App\Action\Modules\Goals;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\Goals\GoalsPaymentsController;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Goals\MyGoalsPayments;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Response\Base\BaseResponse;

#[Route("/module/my-goals-payments", name: "module.my_goals_payments.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_GOALS])]
class GoalsPaymentsAction extends AbstractController {

    public function __construct(
        private readonly GoalsPaymentsController $goalsPaymentsController,
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
        $this->createOrUpdate($request);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $allPayments = $this->goalsPaymentsController->getAllNotDeleted();
        $entriesData = [];
        foreach ($allPayments as $payment) {
            $entriesData[] = $payment->asFrontendData();
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyGoalsPayments $payment
     * @param Request         $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyGoalsPayments $payment, Request $request): JsonResponse
    {
        $this->createOrUpdate($request, $payment);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param MyGoalsPayments $payment
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyGoalsPayments $payment): JsonResponse
    {
        $payment->setDeleted(true);
        $this->em->persist($payment);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request              $request
     * @param MyGoalsPayments|null $payment
     *
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyGoalsPayments $payment = null): void
    {
        if (!$payment) {
            $payment = new MyGoalsPayments();
        }

        $dataArray       = RequestService::tryFromJsonBody($request);
        $name            = ArrayHandler::get($dataArray, 'name');
        $goal            = ArrayHandler::get($dataArray, 'goal');
        $collected       = ArrayHandler::get($dataArray, 'collected');
        $showOnDashboard = ArrayHandler::get($dataArray, 'isForDashboard');
        $startString     = ArrayHandler::get($dataArray, 'start');
        $endString       = ArrayHandler::get($dataArray, 'end');

        $start = new DateTime($startString);
        $end   = new DateTime($endString);

        $payment->setName($name);
        $payment->setMoneyGoal($goal);
        $payment->setMoneyCollected($collected);
        $payment->setCollectionStartDate($start);
        $payment->setDeadline($end);
        $payment->setDisplayOnDashboard($showOnDashboard);

        $this->em->persist($payment);
        $this->em->flush();
    }
}