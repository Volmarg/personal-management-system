<?php

namespace App\Action\Modules\Contacts\Settings;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\Contacts\MyContactsSettingsController;
use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Contacts\MyContactType;
use App\Repository\Modules\Contacts\MyContactTypeRepository;
use App\Response\Base\BaseResponse;
use App\Services\Files\FilesHandler;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/contacts/type", name: "module.contacts.type.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_CONTACTS])]
class TypeAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly MyContactTypeRepository $myContactTypeRepository,
        private readonly MyContactsSettingsController $settingsController
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
        $types = $this->myContactTypeRepository->getAllNotDeleted();

        $entriesData = [];
        foreach ($types as $type) {
            $entriesData[] = [
                'id'        => $type->getId(),
                'name'      => $type->getName(),
                'imagePath' => $type->getImagePath(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyContactType $type
     * @param Request       $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyContactType $type, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $type)->toJsonResponse();
    }

    /**
     * @param MyContactType $type
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyContactType $type): JsonResponse
    {
        $type->setDeleted(true);
        $this->em->persist($type);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * Contains messy legacy code logic
     *
     * @param Request            $request
     * @param MyContactType|null $type
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyContactType $type = null): BaseResponse
    {
        $isNew = is_null($type);
        if ($isNew) {
            $type = new MyContactType();
        }

        $dataArray = RequestService::tryFromJsonBody($request);
        $name      = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $imagePath = ArrayHandler::get($dataArray, 'imagePath', allowEmpty: false);

        // only allow saving already existing entity with unchanged name
        $existingEntity = $this->myContactTypeRepository->getOneByName($name);
        if ((!is_null($existingEntity) && $isNew) || (!$isNew && $type->getName() !== $name && !is_null($existingEntity)) ) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('module.contacts.settings.type.save.nameExist'));
        }

        $normalisedImagePath = FilesHandler::addTrailingSlashIfMissing($imagePath, true);
        if ($isNew) {
            $type->setImagePath($normalisedImagePath);
            $type->setName($name);

            $this->em->persist($type);
            $this->em->flush();
            return BaseResponse::buildOkResponse();
        }

        $typeBeforeUpdate = clone $type;

        $type->setImagePath($normalisedImagePath);
        $type->setName($name);

        $this->em->beginTransaction();
        $this->em->persist($type);
        try {
            $this->settingsController->updateContactsForUpdatedType($typeBeforeUpdate, $type);
        } catch (Exception $e) {
            $this->em->rollback();
            throw $e;
        }
        $this->em->commit();

        return BaseResponse::buildOkResponse();
    }

}