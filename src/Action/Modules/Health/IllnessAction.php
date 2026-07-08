<?php

namespace App\Action\Modules\Health;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Health\DoctorAppointment;
use App\Entity\Modules\Health\Illness;
use App\Exception\MissingDataException;
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

#[Route("/module/health/illness", name: "module.health.illness.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_HEALTH])]
class IllnessAction extends AbstractController
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
        $this->createOrUpdate($request);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $repo = $this->em->getRepository(Illness::class);

        $illnesses = $repo->findBy(['deleted' => false]);
        $illnessesData = array_map(fn(Illness $illness) => [
            'id'           => $illness->getId(),
            'information'  => $illness->getInformation(),
            'name'         => $illness->getName(),
            'appointments' => array_map(fn(DoctorAppointment $doctorAppointment) => [
                'id' => $doctorAppointment->getId(),
            ], $illness->getAppointments()->toArray()),
        ], $illnesses);

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($illnessesData);

        return $response->toJsonResponse();
    }

    /**
     * @param Illness  $illness
     * @param Request $request
     *
     * @return JsonResponse
     * @throws MissingDataException
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(Illness $illness, Request $request): JsonResponse
    {
        $this->createOrUpdate($request, $illness);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Illness $illness
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(Illness $illness): JsonResponse
    {
        $illness->setDeleted(true);
        $this->em->persist($illness);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * Create new entry or update existing
     *
     * @param Request      $request
     * @param Illness|null $illness
     *
     * @throws MissingDataException
     */
    private function createOrUpdate(Request $request, ?Illness $illness = null): void
    {
        if (!$illness) {
            $illness = new Illness();
        }

        $dataArray      = RequestService::tryFromJsonBody($request);
        $name           = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $information    = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $appointmentIds = ArrayHandler::get($dataArray, 'appointmentIds', allowEmpty: false);

        $repo         = $this->em->getRepository(DoctorAppointment::class);
        $appointments = array_map(function ($id) use ($repo) {
            return $repo->find($id);
        }, $appointmentIds);

        $illness->setName($name);
        $illness->setInformation($information);
        $illness->setAppointments($appointments);

        $this->em->persist($illness);
        $this->em->flush();
    }

}