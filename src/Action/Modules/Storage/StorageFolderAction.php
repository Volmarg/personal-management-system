<?php

namespace App\Action\Modules\Storage;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Enum\StorageModuleEnum;
use App\Response\Base\BaseResponse;
use App\Services\Module\Storage\StorageService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles:
 * - manipulating and reading dirs and their content,
 * - direct files manipulation goes to {@see StorageFileAction}
 */
#[Route("/module/storage/folder", name: "module.storage.folder.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_STORAGE])]
class StorageFolderAction extends AbstractController
{
    public function __construct(
        private readonly StorageService $storageService
    ) {
    }

    /**
     * Returns the directory data:
     * - files,
     * - sub-dirs (and theirs files),
     * - files size,
     * - etc.
     *
     * @param string $module
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route("/all/{module}", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(string $module): JsonResponse
    {
        $moduleEnum = StorageModuleEnum::tryFrom($module);
        $entriesData = $this->storageService->getTreeFrontendData($moduleEnum);

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }


}