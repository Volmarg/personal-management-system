<?php

namespace App\Action\Modules\Job;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\Job\MyJobAfterhoursController;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Job\MyJobAfterhours;
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

#[Route("/module/job/afterhours", name: "module.job.afterhours.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_JOB])]
class MyJobAfterhoursAction extends AbstractController
{

    public function __construct(
        private readonly MyJobAfterhoursController $jobAfterhoursController,
        private readonly EntityManagerInterface    $em,
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
        $afterhours = $this->jobAfterhoursController->findAllNotDeletedByType([
            MyJobAfterhours::TYPE_SPENT,
            MyJobAfterhours::TYPE_MADE
        ]);

        $entriesData = [];
        foreach ($afterhours as $afterhour) {
            $entriesData[] = [
                'id'          => $afterhour->getId(),
                'type'        => $afterhour->getType(),
                'minutes'     => $afterhour->getMinutes(),
                'goal'        => $afterhour->getGoal(),
                'description' => $afterhour->getDescription(),
                'date'        => $afterhour->getDate()?->format('Y-m-d'),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyJobAfterhours $afterhour
     * @param Request         $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyJobAfterhours $afterhour, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $afterhour)->toJsonResponse();
    }

    /**
     * @param MyJobAfterhours $afterhour
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyJobAfterhours $afterhour): JsonResponse
    {
        $afterhour->setDeleted(true);
        $this->em->persist($afterhour);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request              $request
     * @param MyJobAfterhours|null $afterhour
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyJobAfterhours $afterhour = null): BaseResponse
    {
        $isNew = is_null($afterhour);
        if ($isNew) {
            $afterhour = new MyJobAfterhours();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $type        = ArrayHandler::get($dataArray, 'type', allowEmpty: false);
        $minutes     = ArrayHandler::get($dataArray, 'minutes', allowEmpty: false);
        $goal        = ArrayHandler::get($dataArray, 'goal', true);
        $description = ArrayHandler::get($dataArray, 'description', allowEmpty: false);
        $dateString  = ArrayHandler::get($dataArray, 'date', allowEmpty: false);

        $afterhour->setType($type);
        $afterhour->setMinutes($minutes);
        $afterhour->setGoal($goal);
        $afterhour->setDescription($description);
        $afterhour->setDate(new DateTime($dateString));

        $this->em->persist($afterhour);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}
