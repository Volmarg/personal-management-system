<?php


namespace App\Action\Modules\Goals;


use App\Annotation\System\ModuleAnnotation;
use App\Entity\Modules\Goals\MyGoalsPayments;
use App\Repository\Modules\Goals\MyGoalsPaymentsRepository;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/my-goals-payments", name: "module.my_goals_payments.")]
#[ModuleAnnotation(values: ["name" => ModulesService::MODULE_NAME_GOALS])]
class GoalsPaymentsAction extends AbstractController {

    public function __construct(
        private readonly MyGoalsPaymentsRepository $goalsPaymentsRepository,
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
        $allPayments = $this->goalsPaymentsRepository->getAllNotDeleted();
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
        $name            = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $goal            = ArrayHandler::get($dataArray, 'goal', allowEmpty: false);
        $collected       = ArrayHandler::get($dataArray, 'collected', allowEmpty: false);
        $showOnDashboard = ArrayHandler::get($dataArray, 'isForDashboard');
        $startString     = ArrayHandler::get($dataArray, 'start', allowEmpty: false);
        $endString       = ArrayHandler::get($dataArray, 'end', allowEmpty: false);

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