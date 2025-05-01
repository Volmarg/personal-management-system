<?php


namespace App\Action\Modules\Job;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Job\MyJobHolidaysPool;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/job/settings/holidays/pool", name: "module.job.holidays.pool.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_JOB])]
class MyJobHolidaysPoolAction extends AbstractController {

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
        $this->createOrUpdate($request);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $entriesData = [];
        $pools       = $this->em->getRepository(MyJobHolidaysPool::class)->getAllNotDeleted();
        foreach ($pools as $pool) {
            $entriesData[] = [
                'id'          => $pool->getId(),
                'year'        => $pool->getYear(),
                'days'        => $pool->getDaysInPool(),
                'companyName' => $pool->getCompanyName(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyJobHolidaysPool $holidayPool
     * @param Request           $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyJobHolidaysPool $holidayPool, Request $request): JsonResponse
    {
        $this->createOrUpdate($request, $holidayPool);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param MyJobHolidaysPool $holidayPool
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyJobHolidaysPool $holidayPool): JsonResponse
    {
        $holidayPool->setDeleted(true);
        $this->em->persist($holidayPool);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request                $request
     * @param MyJobHolidaysPool|null $holidayPool
     *
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyJobHolidaysPool $holidayPool = null): void
    {
        if (!$holidayPool) {
            $holidayPool = new MyJobHolidaysPool();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $year        = ArrayHandler::get($dataArray, 'year', allowEmpty: false);
        $days        = ArrayHandler::get($dataArray, 'days', allowEmpty: false);
        $companyName = ArrayHandler::get($dataArray, 'companyName', allowEmpty: false);

        $holidayPool->setYear($year);
        $holidayPool->setDaysInPool($days);
        $holidayPool->setCompanyName($companyName);

        $this->em->persist($holidayPool);
        $this->em->flush();
    }

}