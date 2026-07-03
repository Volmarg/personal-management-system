<?php

namespace App\Action\Modules\Health;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Health\Doctor;
use App\Entity\Modules\Health\DoctorAppointment;
use App\Entity\Modules\Health\Illness;
use App\Entity\Modules\Storage\StorageFile;
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

#[Route("/module/health/doctor-appointment", name: "module.health.doctor_appointment.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_HEALTH])]
class DoctorAppointmentAction extends AbstractController
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
        $repo = $this->em->getRepository(DoctorAppointment::class);

        $doctorAppointments = $repo->findBy(['deleted' => false]);
        $appointmentsData = array_map(fn(DoctorAppointment $appointment) => [
            'id'            => $appointment->getId(),
            'date'          => $appointment->getDate()->format('Y-m-d'),
            'information'   => $appointment->getId(),
            'illness'       => $appointment->getIllness(),
            'storage_files' => array_map(fn(StorageFile $file) => $file->getId(), $appointment->getStorageFiles()),
            'doctor'        => $appointment->getDoctor()->getId(),
        ], $doctorAppointments);

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($appointmentsData);

        return $response->toJsonResponse();
    }

    /**
     * @param DoctorAppointment $doctorAppointment
     * @param Request           $request
     *
     * @return JsonResponse
     * @throws MissingDataException
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(DoctorAppointment $doctorAppointment, Request $request): JsonResponse
    {
        $this->createOrUpdate($request, $doctorAppointment);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param DoctorAppointment $doctorAppointment
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(DoctorAppointment $doctorAppointment): JsonResponse
    {
        $doctorAppointment->setDeleted(true);
        $this->em->persist($doctorAppointment);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * Create new entry or update existing
     *
     * @param Request                $request
     * @param DoctorAppointment|null $doctorAppointment
     *
     * @throws MissingDataException
     */
    private function createOrUpdate(Request $request, ?DoctorAppointment $doctorAppointment = null): void
    {
        if (!$doctorAppointment) {
            $doctorAppointment = new DoctorAppointment();
        }

        $illnessRepo = $this->em->getRepository(Illness::class);
        $storageFileRepo = $this->em->getRepository(StorageFile::class);

        $dataArray      = RequestService::tryFromJsonBody($request);
        $name           = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $information    = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $doctorId       = ArrayHandler::get($dataArray, 'doctor', allowEmpty: false);
        $date           = ArrayHandler::get($dataArray, 'date', allowEmpty: false);
        $storageFileIds = ArrayHandler::get($dataArray, 'storage_files', allowEmpty: false);
        $illnessId       = ArrayHandler::get($dataArray, 'illnesses', allowEmpty: false);

        $illness = $illnessRepo->find($illnessId);

        $storageFiles = array_map(function ($id) use ($storageFileRepo) {
            return $storageFileRepo->find($id);
        }, $storageFileIds);

        /** @var StorageFile[] $storageFiles */
        $storageFiles = array_filter($storageFiles);

        $doctor = $this->em->getRepository(Doctor::class)->find($doctorId);

        $doctorAppointment->setInformation($information);
        $doctorAppointment->setDoctor($doctor);
        $doctorAppointment->setDate($date);
        $doctorAppointment->setIllness($illness);
        $doctorAppointment->setStorageFiles($storageFiles);

        $this->em->persist($doctorAppointment);
        $this->em->flush();
    }

}