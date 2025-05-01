<?php

namespace App\Services\Module\Storage;

use App\Response\Base\BaseResponse;
use App\Services\Core\Logger;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class StorageFileService
{
    public function __construct(
        private readonly StorageService      $storageService,
        private readonly TranslatorInterface $translator,
        private readonly Logger              $logger
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
                $this->logger->getLogger()->critical("Could not remove file", [
                    'info'          => "unlink function failed",
                    'filePath'      => $filePath,
                    'possibleError' => error_get_last(),
                ]);
            }
        }

        return $notRemovedFiles;
    }

}