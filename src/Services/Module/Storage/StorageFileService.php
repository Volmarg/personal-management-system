<?php

namespace App\Services\Module\Storage;

use App\Entity\Modules\Storage\StorageFile;
use App\Enum\StorageModuleEnum;
use App\Repository\Modules\Storage\StorageFileRepository;
use App\Response\Base\BaseResponse;
use App\Services\Files\PathService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class StorageFileService
{
    public function __construct(
        private readonly StorageService      $storageService,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface     $logger,
        private readonly StorageFileRepository $storageFileRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param string $newPath
     * @param string $oldPath
     * @param string $dirPath
     * @param string $currFileName
     * @param string $newFileName
     *
     * @return BaseResponse|null
     * @throws Exception
     */
    public function validatePreUpdate(string $newPath, string $oldPath, string $dirPath, string $currFileName, string $newFileName): ?BaseResponse
    {
        $this->storageService->ensureStorageManipulation($dirPath);
        $this->storageService->ensureStorageManipulation($newPath);

        if (empty($currFileName)) {
            return BaseResponse::buildOkResponse($this->translator->trans('module.storage.rename.currFileNameIsEmpty'));
        }

        if (empty($newFileName)) {
            return BaseResponse::buildOkResponse($this->translator->trans('module.storage.rename.newFileNameIsEmpty'));
        }

        if (empty($dirPath)) {
            return BaseResponse::buildOkResponse($this->translator->trans('module.storage.rename.dirPathIsEmpty'));
        }

        if (file_exists($newPath) && $oldPath !== $newPath) {
            return BaseResponse::buildOkResponse($this->translator->trans('module.storage.common.fileNameExistInFolder'));
        }

        if (!file_exists($oldPath)) {
            return BaseResponse::buildOkResponse($this->translator->trans('module.storage.common.fileDoesNotExist'));
        }

        if (!is_dir($dirPath)) {
            $msg = $this->translator->trans('module.storage.common.fileUpdateDirPathIsNotFolder') . " :{$dirPath}";
            return BaseResponse::buildOkResponse($msg);
        }

        if (!is_writable($dirPath)) {
            $msg = $this->translator->trans('module.storage.common.folderNotWritable') . " :{$dirPath}";
            return BaseResponse::buildOkResponse($msg);
        }

        return null;
    }

    /**
     * @param array $filesPaths
     *
     * @return array - not removed files paths
     */
    public function removeFiles(array $filesPaths): array
    {
        $notRemovedFiles = [];
        foreach ($filesPaths as $filePath) {
            if (!file_exists($filePath)) {
                $notRemovedFiles[] = $filePath;
                continue;
            }

            if (!is_file($filePath)) {
                $notRemovedFiles[] = $filePath;
                continue;
            }

            $isRemoved = unlink($filePath);
            if (!$isRemoved) {
                $notRemovedFiles[] = $filePath;
                $this->logger->critical("Could not remove file", [
                    'info'          => "unlink function failed",
                    'filePath'      => $filePath,
                    'possibleError' => error_get_last(),
                ]);
            }

            $storageFileEntity = $this->storageFileRepository->findOneBy(['filePath' => $filePath]);
            if (!is_null($storageFileEntity)) {
                $this->entityManager->remove($storageFileEntity);
                $this->entityManager->flush();
            }
        }

        return $notRemovedFiles;
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getFilteredFiles(Request $request): array
    {
        $moduleName  = $request->get("moduleName");
        $queryString = $request->get("queryString");
        $tags        = $request->get("tags");

        $filesData = [];
        if (is_null($moduleName)) {
            foreach (StorageModuleEnum::cases() as $enum) {
                [$storageEntries, $filesData] = $this->storageService->getTreeData($enum, $filesData);
            }
        } else {
            $moduleEnum  = StorageModuleEnum::tryFrom($moduleName);
            [$entriesData, $filesData] = $this->storageService->getTreeData($moduleEnum, $filesData);
        }

        if (!is_null($queryString)) {
            $filesData = array_filter($filesData, function (array $fileData) use ($queryString) {
                return str_contains($fileData['name'], $queryString);
            });
        }

        if (!empty($tags)) {
            $filesData = array_filter($filesData, function (array $fileData) use ($tags) {
                return !empty(array_intersect($fileData['tags'], $tags));
            });
        }

        return $filesData;
    }

    /**
     * @param string $filePath
     * @param bool   $flush
     *
     * @return string|null
     */
    public function uploadedFileIntoEntity(string $filePath, bool $flush = true): ?string
    {
        $storageModule = PathService::getStorageModuleByPath($filePath);
        if ($this->storageFileRepository->exists($filePath)) {
            return null;
        }

        $storageFile = new StorageFile($filePath, $storageModule->value);
        $this->entityManager->persist($storageFile);

        if ($flush) {
            $this->entityManager->flush();
        }

        return $filePath;
    }

}