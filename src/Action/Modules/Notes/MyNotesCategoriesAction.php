<?php

namespace App\Action\Modules\Notes;

use App\Annotation\System\ModuleAnnotation;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\Module\Notes\MyNotesCategoriesService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/my-notes-categories", name: "module.my_notes_categories.")]
#[ModuleAnnotation(values: ["name" => ModulesService::MODULE_NAME_NOTES])]
class MyNotesCategoriesAction extends AbstractController {

    // added this just to make the left side menu look somewhat decent
    private const MAX_NESTING_LEVEL = 4;

    public function __construct(
        private readonly MyNotesCategoriesService $notesCategoriesService,
        private readonly EntityManagerInterface   $em,
        private readonly TranslatorInterface      $translator
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
        $entriesData = [];
        $categories = $this->notesCategoriesService->findAllNotDeleted();
        foreach ($categories as $category) {
            $parentCategory = null;
            if (is_numeric($category->getParentId())) {
                $parentCategory = $this->em->find(MyNotesCategories::class, (int)$category->getParentId());
            }

            $entriesData[] = [
                'id'              => $category->getId(),
                'name'            => $category->getName(),
                'parentId'        => $parentCategory?->getId(),
                'parentName'      => $parentCategory?->getName() ?? '',
                'isParentDeleted' => $parentCategory?->isDeleted() ?? false,
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
     * @param MyNotesCategories $category
     * @param Request           $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyNotesCategories $category, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $category)->toJsonResponse();
    }

    /**
     * @param MyNotesCategories $category
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyNotesCategories $category): JsonResponse
    {
        $category->setDeleted(true);
        $this->em->persist($category);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request                $request
     * @param MyNotesCategories|null $category
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyNotesCategories $category = null): BaseResponse
    {
        if (!$category) {
            $category = new MyNotesCategories();
        }

        $dataArray = RequestService::tryFromJsonBody($request);
        $parentId  = ArrayHandler::get($dataArray, 'parentId', true);
        $name      = ArrayHandler::get($dataArray, 'name', allowEmpty: false);

        // duped child names not allowed
        $childNameExists = $this->notesCategoriesService->hasCategoryChildWithThisName($name, $parentId);
        if ($childNameExists) {
            $msg = $this->translator->trans('module.notes.categories.createdUpdate.childNameExist');
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        // parentId == categoryId is not allowed
        if ($parentId && $parentId === $category->getId()) {
            $msg = $this->translator->trans('module.notes.categories.createdUpdate.categoryIdEqualParentId');
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        $nesting = ($parentId ? 1 : 0);
        $usedParentId = $parentId;
        while ($usedParentId && $parentCategory = $this->em->find(MyNotesCategories::class, $usedParentId)) {
            $usedParentId = $parentCategory->getParentId();
            $nesting++;

            if ($nesting > self::MAX_NESTING_LEVEL) {
                $msg = $this->translator->trans('module.notes.categories.createdUpdate.maxNestingLevel', ["{{max}}" => self::MAX_NESTING_LEVEL]);
                return BaseResponse::buildBadRequestErrorResponse($msg);
            }
        }

        $category->setName($name);
        $category->setParentId($parentId);
        $category->setColor(''); // info: setting only for backward compatibility

        $this->em->persist($category);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}