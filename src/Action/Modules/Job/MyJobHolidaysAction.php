<?php


namespace App\Action\Modules\Job;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Job\MyJobHolidays;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use App\Services\Validation\EntityValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/job/holidays/days-spent", name: "module.job.holidays.days_spent.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_JOB])]
class MyJobHolidaysAction extends AbstractController {

    public function __construct(
        private readonly EntityValidatorService $entityValidator,
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
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $holidays    = $this->em->getRepository(MyJobHolidays::class)->getAllNotDeleted();
        $entriesData = [];
        foreach ($holidays as $holiday) {
            $entriesData[] = [
                'id'          => $holiday->getId(),
                'year'        => $holiday->getYear(),
                'daysSpent'   => $holiday->getDaysSpent(),
                'information' => $holiday->getInformation(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyJobHolidays $holidayEntry
     * @param Request     $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyJobHolidays $holidayEntry, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $holidayEntry)->toJsonResponse();
    }

    /**
     * @param MyJobHolidays $holidayEntry
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyJobHolidays $holidayEntry): JsonResponse
    {
        $holidayEntry->setDeleted(true);
        $this->em->persist($holidayEntry);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request            $request
     * @param MyJobHolidays|null $holidayEntry
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyJobHolidays $holidayEntry = null): BaseResponse
    {
        $isNew = is_null($holidayEntry);
        if ($isNew) {
            $holidayEntry = new MyJobHolidays();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $year        = ArrayHandler::get($dataArray, 'year');
        $days        = ArrayHandler::get($dataArray, 'daysSpent');
        $information = ArrayHandler::get($dataArray, 'information');

        $holidayEntry->setYear($year);
        $holidayEntry->setDaysSpent($days);
        $holidayEntry->setInformation($information);

        if ($isNew) {
            $validationResult = $this->entityValidator->handleValidation($holidayEntry,EntityValidatorService::ACTION_CREATE);
            if (!$validationResult->isValid()) {
                return BaseResponse::buildBadRequestErrorResponse($validationResult->getAllFailedValidationMessagesAsSingleString());
            }
        }

        $this->em->persist($holidayEntry);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}