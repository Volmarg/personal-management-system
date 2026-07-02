<?php

namespace App\Action\Modules\Health;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Health\Doctor;
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

#[Route("/module/health/doctor", name: "module.health.doctor.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_HEALTH])]
class DoctorAction extends AbstractController
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
        $repo = $this->em->getRepository(Doctor::class);

        $doctors = $repo->findBy(['deleted' => false]);
        $doctorsData = array_map(fn(Doctor $doctor) => [
            'id'             => $doctor->getId(),
            'address'        => $doctor->getAddress(),
            'information'    => $doctor->getInformation(),
            'name'           => $doctor->getName(),
            'specialisation' => $doctor->getSpecialisation(),
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
        $this->createOrUpdate($request, $doctor);
        return BaseResponse::buildOkResponse()->toJsonResponse();
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
     * @throws MissingDataException
     */
    private function createOrUpdate(Request $request, ?Doctor $doctor = null): void
    {
        if (!$doctor) {
            $doctor = new Doctor();
        }

        $dataArray      = RequestService::tryFromJsonBody($request);
        $name           = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $information    = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $address        = ArrayHandler::get($dataArray, 'address', allowEmpty: false);
        $specialisation = ArrayHandler::get($dataArray, 'specialisation', allowEmpty: false);

        $doctor->setName($name);
        $doctor->setInformation($information);
        $doctor->setAddress($address);
        $doctor->setSpecialisation($specialisation);

        $this->em->persist($doctor);
        $this->em->flush();
    }

}