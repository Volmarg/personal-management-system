<?php

namespace App\Action\Modules\Storage;

use App\Annotation\System\ModuleAnnotation;
use App\Entity\FilesTags;
use App\Entity\Modules\ModuleData;
use App\Entity\System\LockedResource;
use App\Enum\StorageModuleEnum;
use App\Response\Base\BaseResponse;
use App\Services\Files\PathService;
use App\Services\Module\ModulesService;
use App\Services\Module\Storage\StorageFolderService;
use App\Services\Module\Storage\StorageService;
use App\Services\RequestService;
use App\Services\System\LockedResourceService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
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
#[ModuleAnnotation(values: ["name" => ModulesService::MODULE_NAME_STORAGE])]
class StorageFolderAction extends AbstractController
{
    public function __construct(
        private readonly StorageService         $storageService,
        private readonly TranslatorInterface    $translator,
        private readonly LoggerInterface        $logger,
        private readonly LockedResourceService  $lockedResourceService,
        private readonly EntityManagerInterface $entityManager,
        private readonly StorageFolderService   $storageFolderService
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
        $entriesData = $this->storageService->getTreeData($moduleEnum);

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
            $entriesData[$enum->value] = $this->storageService->getTreeData($enum);
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
        $dataArray     = RequestService::tryFromJsonBody($request);
        $parentDirPath = ArrayHandler::get($dataArray, 'parentDir', allowEmpty: false);
        $newDirName    = ArrayHandler::get($dataArray, 'newDirName', allowEmpty: false);

        $this->storageService->ensureStorageManipulation($parentDirPath);
        PathService::validatePathSafety($newDirName);
        PathService::validatePathSafety($parentDirPath);

        $newDirPath = $parentDirPath . DIRECTORY_SEPARATOR . $newDirName;
        $response = $this->storageFolderService->validateCreateAndRename($parentDirPath, $newDirName, $newDirPath);
        if (!is_null($response)) {
            return $response->toJsonResponse();
        }

        if (!mkdir($newDirPath)) {
            $msg = $this->translator->trans('module.storage.common.error');
            $this->logger->critical($msg, [
                'info'          => "rename function failed",
                'parentDir'     => $parentDirPath,
                'newDirPath'    => $newDirPath,
                'possibleError' => error_get_last(),
            ]);
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        $msg = $this->translator->trans('module.storage.newFolder.success');
        $response = BaseResponse::buildOkResponse($msg);

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
            $isLocked = $this->lockedResourceService->isResourceLocked($checkedPath, LockedResource::TYPE_DIRECTORY, $moduleName);
            if ($isLocked && $checkedPath !== $dir) {
                $msg = $this->translator->trans('module.storage.lock.parentDirIsLocked', ['{{path}}' => $checkedPath]);
                return BaseResponse::buildToggleLockResponse(true, $msg, Response::HTTP_BAD_REQUEST)->toJsonResponse();
            }
        }

        $isLocked = $this->lockedResourceService->toggleLock($dir, LockedResource::TYPE_DIRECTORY, $moduleName);
        $msg = $this->translator->trans('module.storage.lock.isLocked');
        if (!$isLocked) {
            $msg = $this->translator->trans('module.storage.lock.isUnlocked');
        }

        return BaseResponse::buildToggleLockResponse($isLocked, $msg)->toJsonResponse();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route("/folder-meta-data", name: "folder_meta_data", methods: [Request::METHOD_POST])]
    public function updateFolderData(Request $request): JsonResponse
    {
        $dataArray   = RequestService::tryFromJsonBody($request);
        $moduleName  = ArrayHandler::get($dataArray, 'moduleName');
        $dirPath     = ArrayHandler::get($dataArray, 'dirPath');
        $description = ArrayHandler::get($dataArray, 'description');

        $entity = $this->entityManager->getRepository(ModuleData::class)->findOneBy(['recordIdentifier' => $dirPath]);
        $entity ??= new ModuleData();

        $moduleEnum = StorageModuleEnum::tryFrom($moduleName);

        $entity->setDescription($description);
        $entity->setRecordType(ModuleData::RECORD_TYPE_DIRECTORY);
        $entity->setRecordIdentifier($dirPath);
        $entity->setModule(StorageService::enumToModuleName($moduleEnum));

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }
    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route("/remove", name: "remove", methods: [Request::METHOD_POST])]
    public function remove(Request $request): JsonResponse
    {
        $dataArray = RequestService::tryFromJsonBody($request);
        $dirPath   = ArrayHandler::get($dataArray, 'dirPath');
        if (in_array($dirPath, PathService::getAllStorageBaseDirs())) {
            $msg = $this->translator->trans('module.storage.remove.cannotRemoveStorageBaseDir');
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        PathService::validatePathSafety($dirPath);

        try {
            $this->entityManager->beginTransaction();

            $lock = $this->entityManager->getRepository(LockedResource::class)->findByDirectoryLocation($dirPath);
            $data = $this->entityManager->getRepository(ModuleData::class)->findOneBy(['recordIdentifier' => $dirPath]);
            $tags = $this->entityManager->getRepository(FilesTags::class)->findByDirPath($dirPath);

            if (!empty($lock)) {
                $this->entityManager->remove($lock);
            }

            if (!empty($data)) {
                $this->entityManager->remove($data);
            }

            foreach ($tags as $tag) {
                $this->entityManager->remove($tag);
            }

            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $msg = $this->translator->trans('module.storage.common.error');
            $this->logger->critical($msg, [
                "info"  => "Could not remove related entities",
                "trace" => $e->getTraceAsString(),
                "msg"   => $e->getMessage(),
            ]);
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        (new Filesystem())->remove($dirPath);

        $this->entityManager->commit();

        $msg = $this->translator->trans('module.storage.remove.folderHasBeenRemoved');
        return BaseResponse::buildOkResponse($msg)->toJsonResponse();
    }

    /**
     * Will rename existing dir inside storage module structure
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/rename", name: "rename", methods: [Request::METHOD_POST])]
    public function renameFolder(Request $request): JsonResponse
    {
        $dataArray   = RequestService::tryFromJsonBody($request);
        $currDirPath = ArrayHandler::get($dataArray, 'currDirPath');
        $dirNewName  = ArrayHandler::get($dataArray, 'newDirName');

        $this->storageService->ensureStorageManipulation($currDirPath);
        PathService::validatePathSafety($dirNewName);
        PathService::validatePathSafety($currDirPath);

        $newDirPath = dirname($currDirPath) . DIRECTORY_SEPARATOR . $dirNewName;
        $response = $this->storageFolderService->validateCreateAndRename($currDirPath, $dirNewName, $newDirPath);
        if (!is_null($response)) {
            return $response->toJsonResponse();
        }

        if (!rename($currDirPath, $newDirPath)) {
            $msg = $this->translator->trans('module.storage.common.error');
            $this->logger->critical($msg, [
                'info'          => "rename function failed",
                'parentDir'     => $currDirPath,
                'newDirPath'    => $newDirPath,
                'possibleError' => error_get_last(),
            ]);
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        $repo = $this->entityManager->getRepository(ModuleData::class);
        $moduleData = $repo->getOneByRecordTypeModuleAndRecordIdentifier(
            ModuleData::RECORD_TYPE_DIRECTORY,
            ModulesService::MODULE_NAME_FILES,
            $currDirPath
        );

        if (!is_null($moduleData)) {
            $moduleData->setRecordIdentifier($newDirPath);
            $this->entityManager->persist($moduleData);
            $this->entityManager->flush();
        }

        $msg = $this->translator->trans('module.storage.renameFolder.success');
        $response = BaseResponse::buildOkResponse($msg);

        return $response->toJsonResponse();
    }

    /**
     * @throws Exception
     */
    #[Route("/move-or-copy", name: "move_or_copy", methods: [Request::METHOD_POST])]
    public function moveFiles(Request $request): JsonResponse
    {
        $dataArray        = RequestService::tryFromJsonBody($request);
        $currDirPath      = ArrayHandler::get($dataArray, 'currDirPath');
        $newDirParentPath = ArrayHandler::get($dataArray, 'newDirParentPath');
        $move             = ArrayHandler::get($dataArray, 'move');
        $targetModuleName = ArrayHandler::get($dataArray, 'targetModuleName');

        if (!file_exists($currDirPath)) {
            $msg = $this->translator->trans('module.storage.common.folderNotExist') . $currDirPath;
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        if (!file_exists($newDirParentPath)) {
            $msg = $this->translator->trans('module.storage.common.folderNotExist') . $newDirParentPath;
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        $this->storageService->ensureStorageManipulation($currDirPath);
        $this->storageService->ensureStorageManipulation($newDirParentPath);

        $response = $this->storageFolderService->moveOrCopyFolder($currDirPath, $newDirParentPath, $move, $targetModuleName);

        return $response->toJsonResponse();
    }

}