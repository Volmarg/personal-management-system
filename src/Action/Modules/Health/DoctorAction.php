<?php

namespace App\Action\Modules\Health;

use App\Attribute\ModuleAttribute;
use App\DTO\Modules\Health\DoctorContactDto;
use App\Entity\Modules\Health\Doctor;
use App\Exception\MissingDataException;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use App\Traits\SerializerAwareTrait;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/health/doctor", name: "module.health.doctor.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_HEALTH])]
class DoctorAction extends AbstractController
{
    use SerializerAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
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
        return $this->createOrUpdate($request);
    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $repo = $this->em->getRepository(Doctor::class);

        $doctors = $repo->findBy(['deleted' => false]);
        $doctorsData = array_map(fn(Doctor $doctor) => [
            'id'             => $doctor->getId(),
            'address'        => $doctor->getAddress(),
            'information'    => $doctor->getInformation(),
            'name'           => $doctor->getName(),
            'specialisation' => $doctor->getSpecialisation(),
            'contacts'       => $doctor->getContacts(),
        ], $doctors);

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($doctorsData);

        return $response->toJsonResponse();
    }

    /**
     * @param Doctor  $doctor
     * @param Request $request
     *
     * @return JsonResponse
     * @throws MissingDataException
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(Doctor $doctor, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $doctor);
    }

    /**
     * @param Doctor $doctor
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(Doctor $doctor): JsonResponse
    {
        $doctor->setDeleted(true);
        $this->em->persist($doctor);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * Create new entry or update existing
     *
     * @param Request     $request
     * @param Doctor|null $doctor
     *
     * @return JsonResponse
     * @throws MissingDataException
     */
    private function createOrUpdate(Request $request, ?Doctor $doctor = null): JsonResponse
    {
        if (!$doctor) {
            $doctor = new Doctor();
        }

        $dataArray      = RequestService::tryFromJsonBody($request);
        $name           = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $information    = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $address        = ArrayHandler::get($dataArray, 'address', allowEmpty: false);
        $specialisation = ArrayHandler::get($dataArray, 'specialisation', allowEmpty: false);
        $contacts       = ArrayHandler::get($dataArray, 'contacts', allowEmpty: true);

        foreach ($contacts as $contact) {
            /** @var DoctorContactDto $contactDto */
            $contactDto = $this->deserialize($contact, DoctorContactDto::class);
            if (empty($contactDto->getName()) || empty($contactDto->getValue())) {
                $msg = $this->translator->trans('module.doctor.contacts.message.emptyData');
                return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
            }
        }

        $doctor->setName($name);
        $doctor->setInformation($information);
        $doctor->setAddress($address);
        $doctor->setSpecialisation($specialisation);
        $doctor->setContacts($contacts);

        try {
            $this->em->persist($doctor);
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            $msg = $this->translator->trans('module.generic.message.duplicate');
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        $response = BaseResponse::buildOkResponse();
        $response->addData(BaseResponse::KEY_DATA_ID, $doctor->getId());

        return $response->toJsonResponse();
    }

}