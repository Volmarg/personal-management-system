<?php

namespace App\Action\Modules\Health;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Health\Doctor;
use App\Entity\Modules\Health\DoctorAppointment;
use App\Entity\Modules\Health\Illness;
use App\Entity\Modules\Storage\StorageFile;
use App\Exception\MissingDataException;
use App\Response\Base\BaseResponse;
use App\Services\Module\Health\DoctorService;
use App\Services\Module\ModulesService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use App\Traits\ExceptionLoggerAwareTrait;
use DateMalformedStringException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/health/doctor-appointment", name: "module.health.doctor_appointment.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_HEALTH])]
class DoctorAppointmentAction extends AbstractController
{
    use ExceptionLoggerAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
        private readonly DoctorService $doctorService
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
            'date'          => $appointment->getDate()->format('Y-m-d H:i:s'),
            'information'   => $appointment->getId(),
            'illness'       => $appointment->getIllness()->getId(),
            'storage_files' => array_map(fn(StorageFile $file) => $file->getId(), $appointment->getStorageFiles()),
            'doctor'        => [
                'id'   => $appointment->getDoctor()->getId(),
                'name' => $appointment->getDoctor()->getName(),
            ]
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
    #[Route("/{id<[0-9]+>}", name: "update", methods: [Request::METHOD_PATCH])]
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
    #[Route("/{id<[0-9]+>}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(DoctorAppointment $doctorAppointment): JsonResponse
    {
        $doctorAppointment->setDeleted(true);
        $this->em->persist($doctorAppointment);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * Special route for saving files for appointments
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws MissingDataException
     */
    #[Route("/save-files", name: "save_files", methods: [Request::METHOD_PATCH])]
    public function saveFiles(Request $request): JsonResponse
    {
        $dataArray        = RequestService::tryFromJsonBody($request);
        $appointmentsData = ArrayHandler::get($dataArray, 'appointmentsData');
        $illnessId        = ArrayHandler::get($dataArray, 'illnessId', allowEmpty: false);

        try {
            $this->em->beginTransaction();
            $this->doctorService->handleFiles($illnessId, $appointmentsData);
            $this->em->flush();
            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();
            $this->logException($e);

            return BaseResponse::buildInternalServerErrorResponse()->toJsonResponse();
        }

        return BaseResponse::buildOkResponse($this->translator->trans('module.health.files.message.filesSaveSuccess'))->toJsonResponse();
    }

    /**
     * Create new entry or update existing
     *
     * @param Request                $request
     * @param DoctorAppointment|null $doctorAppointment
     *
     * @throws MissingDataException
     * @throws DateMalformedStringException
     */
    private function createOrUpdate(Request $request, ?DoctorAppointment $doctorAppointment = null): void
    {
        if (!$doctorAppointment) {
            $doctorAppointment = new DoctorAppointment();
        }

        $illnessRepo = $this->em->getRepository(Illness::class);

        $dataArray      = RequestService::tryFromJsonBody($request);
        $information    = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $doctorId       = ArrayHandler::get($dataArray, 'doctor', allowEmpty: false);
        $date           = ArrayHandler::get($dataArray, 'date', allowEmpty: false);
        $illnessId      = ArrayHandler::get($dataArray, 'illness', allowEmpty: false);

        $illness = $illnessRepo->find($illnessId);

        $doctor = $this->em->getRepository(Doctor::class)->find($doctorId);

        $doctorAppointment->setInformation($information);
        $doctorAppointment->setDoctor($doctor);
        $doctorAppointment->setDate(new DateTime($date));
        $doctorAppointment->setIllness($illness);

        $this->em->persist($doctorAppointment);
        $this->em->flush();
    }

}