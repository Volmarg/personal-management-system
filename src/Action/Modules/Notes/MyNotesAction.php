<?php

namespace App\Action\Modules\Notes;

use App\Annotation\System\ModuleAnnotation;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Entity\System\LockedResource;
use App\Repository\Modules\Notes\MyNotesRepository;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\RequestService;
use App\Services\System\LockedResourceService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/my-notes", name: "module.my_notes.")]
#[ModuleAnnotation(values: ["name" => ModulesService::MODULE_NAME_NOTES])]
class MyNotesAction extends AbstractController {

    public function __construct(
        private readonly LockedResourceService  $lockedResourceService,
        private readonly EntityManagerInterface $em,
        private readonly MyNotesRepository      $notesRepository,
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
     * @param MyNotesCategories $category
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    #[Route("/all/{id}", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(MyNotesCategories $category): JsonResponse
    {
        $entriesData = [];
        $notes = $this->notesRepository->getNotesByCategoriesIds([$category->getId()]);
        foreach ($notes as $note) {
            $canSee = $this->lockedResourceService->isAllowedToSeeResource(
                $note->getId(),
                LockedResource::TYPE_ENTITY,
                ModulesService::MODULE_NAME_NOTES,
                false
            );

            if (!$canSee) {
                continue;
            }

            $entriesData[] = [
                "id"         => $note->getId(),
                "categoryId" => $note->getCategory()->getId(),
                "title"      => $note->getTitle(),
                "body"       => $note->getBody(),
                "isLocked"   => $this->lockedResourceService->isResourceLocked(
                    $note->getId(),
                    LockedResource::TYPE_ENTITY,
                    ModulesService::MODULE_NAME_NOTES
                ),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyNotesCategories $category
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "get", methods: [Request::METHOD_GET])]
    public function getOne(MyNotesCategories $category): JsonResponse
    {
        $response = BaseResponse::buildOkResponse();
        $response->setSingleRecordData([
            'id'   => $category->getId(),
            'name' => $category->getName(),
        ]);

        return $response->toJsonResponse();
    }

    /**
     * @param MyNotes $note
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyNotes $note, Request $request): JsonResponse
    {
        $this->createOrUpdate($request, $note);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param MyNotes $note
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/toggle-lock/{id}", name: "toggleLock", methods: [Request::METHOD_PATCH])]
    public function toggleLock(MyNotes $note): JsonResponse
    {
        $isLocked = $this->lockedResourceService->toggleLock(
            $note->getId(),
            LockedResource::TYPE_ENTITY,
            ModulesService::MODULE_NAME_NOTES
        );

        return BaseResponse::buildToggleLockResponse($isLocked)->toJsonResponse();
    }

    /**
     * @param MyNotes $note
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyNotes $note): JsonResponse
    {
        $note->setDeleted(true);
        $this->em->persist($note);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request      $request
     * @param MyNotes|null $note
     *
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyNotes $note = null): void
    {
        if (!$note) {
            $note = new MyNotes();
        }

        $dataArray  = RequestService::tryFromJsonBody($request);
        $title      = ArrayHandler::get($dataArray, 'title', allowEmpty: false);
        $body       = ArrayHandler::get($dataArray, 'body', allowEmpty: false);
        $categoryId = ArrayHandler::get($dataArray, 'category');

        $category = $this->em->find(MyNotesCategories::class, $categoryId);
        if (is_null($category)) {
            throw new Exception("No notes category was found for id: {$categoryId}");
        }

        $note->setTitle($title);
        $note->setBody($body);
        $note->setCategory($category);

        $this->em->persist($note);
        $this->em->flush();
    }

}