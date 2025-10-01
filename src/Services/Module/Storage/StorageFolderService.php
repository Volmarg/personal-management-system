<?php

namespace App\Services\Module\Storage;

use App\Entity\FilesTags;
use App\Entity\Modules\ModuleData;
use App\Entity\System\LockedResource;
use App\Response\Base\BaseResponse;
use App\Services\Files\PathService;
use App\Traits\ExceptionLoggerAwareTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\Translation\TranslatorInterface;

class StorageFolderService
{
    use ExceptionLoggerAwareTrait;

    public function __construct(
        private readonly TranslatorInterface    $translator,
        private readonly LoggerInterface        $logger,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param string $existingDirPath
     * @param string $newDirName
     * @param string $newDirPath
     *
     * @return BaseResponse|null
     */
    public function validateCreateAndRename(string $existingDirPath, string $newDirName, string $newDirPath): ?BaseResponse
    {
        if (empty($newDirName)) {
            $msg = $this->translator->trans('module.storage.common.dirNameIsEmpty');
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        if (empty($existingDirPath)) {
            $msg = $this->translator->trans('module.storage.common.existingDirPath');
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        if (!file_exists($existingDirPath)) {
            $msg = $this->translator->trans('module.storage.common.folderNotExist') . $existingDirPath;
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        if (file_exists($newDirPath) && is_dir($newDirPath)) {
            $msg = $this->translator->trans('module.storage.common.dirExists');
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        if (!is_writable($existingDirPath)) {
            $msg = $this->translator->trans('module.storage.common.dirNotWritable') . " {$existingDirPath}";
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        return null;
    }

    /**
     * Handles moving or copying folder, or its content around
     *
     * @param string $oldDirPath
     * @param string $newDirParentPath
     * @param bool   $moveDir
     * @param string $targetModuleName
     *
     * @return BaseResponse
     * @throws Exception
     */
    public function moveOrCopyFolder(string $oldDirPath, string $newDirParentPath, bool $moveDir, string $targetModuleName): BaseResponse
    {
        if (str_contains($newDirParentPath, $oldDirPath)) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('module.storage.moveFileOrDir.parentIntoDirNotAllowed'));
        }

        if ($moveDir) {
            if (in_array($oldDirPath, PathService::getAllStorageBaseDirs())) {
                $msg = $this->translator->trans('module.storage.moveOrCopyFolder.cannotMoveBaseDir');
                return BaseResponse::buildBadRequestErrorResponse($msg);
            }

            $newDirPath = $newDirParentPath . DIRECTORY_SEPARATOR . basename($oldDirPath);
            if (file_exists($newDirPath)) {
                return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('module.storage.moveFileOrDir.folderWithThisNameAlreadyExist'));
            }

            return $this->moveFolder($oldDirPath, $newDirPath, $targetModuleName);
        }

        return $this->copyFilesFromDir($oldDirPath, $newDirParentPath);
    }

    /**
     * @param string $oldDirPath
     * @param string $newDirPath
     * @param string $targetModuleName
     *
     * @return BaseResponse
     */
    private function moveFolder(string $oldDirPath, string $newDirPath, string $targetModuleName): BaseResponse
    {
        if (!rename($oldDirPath, $newDirPath)) {
            $msg = $this->translator->trans('module.storage.moveFileOrDir.unknownError');
            $this->logger->critical($msg, [
                'info'          => "rename function failed",
                'oldDirPath'    => $oldDirPath,
                'newDirPath'    => $newDirPath,
                'possibleError' => error_get_last(),
            ]);

            return BaseResponse::buildInternalServerErrorResponse($msg);
        }

        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->getRepository(FilesTags::class)->updateFilePathByFolderPathChange($oldDirPath, $newDirPath);

            // skipping module match here, it's not really needed for path based entry
            $data = $this->entityManager->getRepository(ModuleData::class)->findOneBy(['recordIdentifier' => $oldDirPath]);
            if (!is_null($data)) {
                $data->setRecordIdentifier($newDirPath);
                $this->entityManager->persist($data);
            }

            $lock = $this->entityManager->getRepository(LockedResource::class)->findByDirectoryLocation($oldDirPath);
            if (!is_null($lock)) {
                $lock->setRecord($newDirPath);
                $lock->setTarget($targetModuleName);
                $this->entityManager->persist($lock);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->logException($e, [
                'info' => "something went wrong during db updates"
            ]);

            if (!rename($newDirPath, $oldDirPath)) {
                $msg = $this->translator->trans('module.storage.moveFileOrDir.unknownError');
                $this->logger->critical($msg, [
                    'info'          => "could not move back folder, from {$newDirPath} to {$oldDirPath}",
                    'possibleError' => error_get_last(),
                ]);

                $msg = $this->translator->trans('module.storage.moveFileOrDir.folderMovedDatabaseChangesFailed');
                return BaseResponse::buildInternalServerErrorResponse($msg);
            }

            $msg = $this->translator->trans('module.storage.moveFileOrDir.folderCouldNotBeMovedReverted');
            return BaseResponse::buildInternalServerErrorResponse($msg);
        }

        return BaseResponse::buildOkResponse($this->translator->trans('module.storage.moveFileOrDir.folderHaveBeenMoved'));
    }

    /**
     * @param string $oldDirPath
     * @param string $newDirPath
     *
     * @return BaseResponse
     */
    public function copyFilesFromDir(string $oldDirPath, string $newDirPath): BaseResponse
    {
        $copiedFilePaths = [];
        $someFilesExisted = false;
        foreach ((new Finder())->files()->in($oldDirPath)->depth(0) as $file) {
            $newFilePath = $newDirPath . DIRECTORY_SEPARATOR . $file->getFilename();
            if (file_exists($newFilePath)) {
                $postfix = "_" . (new DateTime())->format('Y_m_d_H_i_s');
                $newFilePath = $newDirPath . DIRECTORY_SEPARATOR . $file->getFilenameWithoutExtension() . $postfix . "." . $file->getExtension();
                $someFilesExisted = true;
            }

            $tags = $this->entityManager->getRepository(FilesTags::class)->findOneBy(['fullFilePath' => $file->getPathname()]);
            if (!is_null($tags)) {
                $clonedEntity = clone $tags;
                $clonedEntity->setId(null);
                $clonedEntity->setFullFilePath($newFilePath);
                $this->entityManager->persist($clonedEntity);
            }

            $isCopied = copy($file->getPathname(), $newFilePath);
            if ($isCopied) {
                $copiedFilePaths[] = $newFilePath;
                continue;
            }

            $this->logger->critical("Could not copy {$file->getPathname()} to {$newFilePath}", [
                'possibleError' => error_get_last(),
            ]);

            foreach ($copiedFilePaths as $filePath) {
                if (!unlink($filePath)) {
                    $this->logger->critical("Could not remove copied file: {$filePath}", [
                        'possibleError' => error_get_last(),
                    ]);
                }
            }

            return BaseResponse::buildOkResponse($this->translator->trans('module.storage.common.errorReverted'));
        }

        // skipping module match here, it's not really needed for path based entry
        $data = $this->entityManager->getRepository(ModuleData::class)->findOneBy(['recordIdentifier' => $oldDirPath]);
        if (!is_null($data)) {
            $clonedEntity = clone $data;
            $clonedEntity->setId(null);
            $clonedEntity->setRecordIdentifier($newDirPath);

            $this->entityManager->persist($data);
        }

        $this->entityManager->flush();

        if ($someFilesExisted) {
            return BaseResponse::buildOkResponse($this->translator->trans('module.storage.moveFileOrDir.filesHaveBeenCopiedButExisted'));
        }

        return BaseResponse::buildOkResponse($this->translator->trans('module.storage.moveFileOrDir.filesHaveBeenCopied'));
    }
}