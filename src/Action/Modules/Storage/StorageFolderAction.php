<?php

namespace App\Action\Modules\Storage;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Controller\System\LockedResourceController;
use App\Entity\System\LockedResource;
use App\Enum\StorageModuleEnum;
use App\Response\Base\BaseResponse;
use App\Services\Core\Logger;
use App\Services\Files\PathService;
use App\Services\Module\Storage\StorageService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        private readonly StorageService           $storageService,
        private readonly TranslatorInterface      $translator,
        private readonly Logger                   $logger,
        private readonly LockedResourceController $lockedResourceController,
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
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    #[Route("/all/{module}", name: "get_module_all", methods: [Request::METHOD_GET])]
    public function getModuleAll(string $module): JsonResponse
    {
        $moduleEnum = StorageModuleEnum::tryFrom($module);
        $entriesData = $this->storageService->getTreeFrontendData($moduleEnum);

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * Returns all storage directories data:
     * - files,
     * - sub-dirs (and theirs files),
     * - files size,
     * - etc.
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $entriesData = [];
        foreach (StorageModuleEnum::cases() as $enum) {
            $entriesData[$enum->value] = $this->storageService->getTreeFrontendData($enum);
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * Will create new dir inside storage module structure
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/create", name: "create", methods: [Request::METHOD_POST])]
    public function createFolder(Request $request): JsonResponse
    {
        $dataArray  = RequestService::tryFromJsonBody($request);
        $parentDir  = ArrayHandler::get($dataArray, 'parentDir');
        $newDirName = ArrayHandler::get($dataArray, 'newDirName');

        $this->storageService->ensureStorageManipulation($parentDir);
        PathService::validatePathSafety($newDirName);
        PathService::validatePathSafety($parentDir);

        $newDirPath = $parentDir . DIRECTORY_SEPARATOR . $newDirName;
        if (empty($newDirName)) {
            $msg = $this->translator->trans('module.storage.newFolder.dirNameIsEmpty');
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        if (empty($parentDir)) {
            $msg = $this->translator->trans('module.storage.newFolder.parentDirPathEmpty');
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        if (file_exists($newDirPath) && is_dir($newDirPath)) {
            $msg = $this->translator->trans('module.storage.newFolder.dirExists');
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        if (!is_writable($parentDir)) {
            $msg = $this->translator->trans('module.storage.newFolder.dirNotWritable') . " {$parentDir}";
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        if (!mkdir($newDirPath)) {
            $msg = $this->translator->trans('module.storage.newFolder.errorCreatingDir');
            $this->logger->getLogger()->critical($msg, [
                'info'          => "rename function failed",
                'parentDir'     => $parentDir,
                'newDirPath'    => $newDirPath,
                'possibleError' => error_get_last(),
            ]);
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        $response = BaseResponse::buildOkResponse();

        return $response->toJsonResponse();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    #[Route("/toggle-lock", name: "toggleLock", methods: [Request::METHOD_POST])]
    public function toggleLock(Request $request): JsonResponse
    {
        $dataArray  = RequestService::tryFromJsonBody($request);
        $dir        = ArrayHandler::get($dataArray, 'dir');
        $moduleName = ArrayHandler::get($dataArray, 'moduleName');

        $dirParts    = explode(DIRECTORY_SEPARATOR, $dir);
        $checkedPath = "";
        foreach ($dirParts as $dirPart) {
            $checkedPath .= (!empty($checkedPath) ? DIRECTORY_SEPARATOR : "") . $dirPart;
            $isLocked = $this->lockedResourceController->isResourceLocked($checkedPath, LockedResource::TYPE_DIRECTORY, $moduleName);
            if ($isLocked && $checkedPath !== $dir) {
                $msg = $this->translator->trans('module.storage.lock.parentDirIsLocked', ['{{path}}' => $checkedPath]);
                return BaseResponse::buildToggleLockResponse(true, $msg, Response::HTTP_BAD_REQUEST)->toJsonResponse();
            }
        }

        $isLocked = $this->lockedResourceController->toggleLock($dir, LockedResource::TYPE_DIRECTORY, $moduleName);
        $msg = $this->translator->trans('module.storage.lock.isLocked');
        if (!$isLocked) {
            $msg = $this->translator->trans('module.storage.lock.isUnlocked');
        }

        return BaseResponse::buildToggleLockResponse($isLocked, $msg)->toJsonResponse();
    }

}