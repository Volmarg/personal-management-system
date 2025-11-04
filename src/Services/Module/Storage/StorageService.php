<?php

namespace App\Services\Module\Storage;

use App\Controller\Core\Env;
use App\Entity\FilesTags;
use App\Entity\Modules\ModuleData;
use App\Entity\System\LockedResource;
use App\Enum\StorageModuleEnum;
use App\Response\Base\BaseResponse;
use App\Services\Files\PathService;
use App\Services\Module\ModulesService;
use App\Services\Shell\ShellTreeService;
use App\Services\System\LockedResourceService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StorageService
{
    public function __construct(
        private readonly KernelInterface        $kernel,
        private readonly ShellTreeService       $shellTreeService,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface        $logger,
        private readonly LockedResourceService  $lockedResourceService,
        private readonly TranslatorInterface    $translator,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Recursively walks over the directory tree, building the frontend ready tree
     *
     * @param StorageModuleEnum $module
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function getTreeData(StorageModuleEnum $module, array &$filesData = []): array
    {
        $basePath    = PathService::getStorageModuleBaseDir($module);
        $scannedPath = PathService::setTrailingDirSeparator($this->kernel->getProjectDir()) . $basePath;

        $json    = $this->shellTreeService->getDirJsonTree($scannedPath);
        $mainArr = json_decode($json, true);

        return $this->traverseContent($mainArr, $module, $filesData);
    }

    /**
     * This function should be called whenever some path (file/dir) based manipulations are made with storage logic,
     * this simple method ensures that there won't be a case where someone tries to manipulate project files.
     *
     * @param string $path
     *
     * @throws Exception
     */
    public function ensureStorageManipulation(string $path): void
    {
        PathService::validatePathSafety($path);
        if (!str_contains($path, Env::getUploadDir())) {
            $msg = "Trying to manipulate files outside of " . Env::getUploadDir() . " directory - this is not allowed. Path: {$path}";
            throw new Exception($msg);
        }
    }

    /**
     * Handles moving files / folders around
     *
     * @param string $oldDirPath
     * @param string $newDirPath
     * @param array  $filesNames
     *
     * @return BaseResponse
     * @throws Exception
     */
    public function moveFiles(string $oldDirPath, string $newDirPath, array $filesNames = []): BaseResponse
    {
        if ($oldDirPath === $newDirPath) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('module.storage.moveFileOrDir.oldPathEqualsNewPath'));
        }

        $failedFiles = [];
        foreach ($filesNames as $fileName) {
            $oldFilePath = $oldDirPath . DIRECTORY_SEPARATOR . $fileName;
            $newFilePath = $newDirPath . DIRECTORY_SEPARATOR . $fileName;
            if (file_exists($newFilePath)) {
                $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
                $extension      = pathinfo($fileName, PATHINFO_EXTENSION);
                $dateTimeStamp  = (new DateTime())->format('Y_m_d_H_i_s');

                $newFileName = $nameWithoutExt . Uuid::uuid4() . "_" . $dateTimeStamp . "." . $extension;
                $newFilePath = $newDirPath . DIRECTORY_SEPARATOR . "_" . $newFileName;
            }

            $isRenamed = rename($oldFilePath, $newFilePath);
            if (!$isRenamed) {
                $this->logger->error("Could not copy file. It's going to be skipped.", [
                    'oldFilePath' => $oldFilePath,
                    'newFilePath' => $newFilePath,
                ]);

                $failedFiles[] = $fileName;
            }

            $tags = $this->entityManager->getRepository(FilesTags::class)->getFileTagsEntityByFileFullPath($oldFilePath);
            if ($tags) {
                $tags->setFullFilePath($newFilePath);
                $this->entityManager->persist($tags);
            }
        }

        $this->entityManager->flush();

        if (!empty($failedFiles)) {
            $msg = $this->translator->trans('module.storage.moveFileOrDir.failedCopySomeFiles') . json_encode($failedFiles);
            return BaseResponse::buildInternalServerErrorResponse($msg);
        }

        return BaseResponse::buildOkResponse($this->translator->trans('module.storage.moveFileOrDir.filesHaveBeenMoved'));
    }

    /**
     * @param StorageModuleEnum $enum
     *
     * @return string
     */
    public static function enumToModuleName(StorageModuleEnum $enum): string
    {
        return match ($enum->value) {
            StorageModuleEnum::VIDEOS->value => ModulesService::MODULE_NAME_VIDEO,
            StorageModuleEnum::IMAGES->value => ModulesService::MODULE_NAME_IMAGES,
            StorageModuleEnum::FILES->value => ModulesService::MODULE_NAME_FILES,
            default => throw new \LogicException("Unsupported module {$enum->value}")
        };
    }

    /**
     * Recursively walks over the directory tree
     *
     * @param array             $nodes
     * @param StorageModuleEnum $module
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Driver\Exception0
     * @throws \Doctrine\DBAL\Exception
     * @throws JWTDecodeFailureException
     */
    private function traverseContent(array $nodes, StorageModuleEnum $module, &$filesData): array
    {
        $normalisedNode = [];
        foreach ($nodes as $nodeData) {
            $isDirNode = ($nodeData['type'] === 'directory');
            if (!$isDirNode) {
                continue;
            }

            $humanPath = preg_replace("#.*" . Env::getUploadDir() . "#", "", $nodeData['name']);
            $path      = Env::getUploadDir() . $humanPath;

            $isLocked = $this->lockedResourceService->isResourceLocked($path, LockedResource::TYPE_DIRECTORY, $module->value);
            $isSystemLocked = $this->lockedResourceService->isSystemLocked();
            if ($isLocked && $isSystemLocked) {
                continue;
            }

            $children = [];
            if (array_key_exists('contents', $nodeData)) {
                $children = $this->traverseContent($nodeData['contents'] ?? [], $module, $filesData);
            }

            $basename = basename($nodeData['name']);
            $files    = $this->getDirNodeFiles($nodeData['contents'] ?? [], $humanPath);
            $filesData = [
                ...$filesData,
                ...$files,
            ];

            // skipping module match here, it's not really needed for path based entry
            $data = $this->entityManager->getRepository(ModuleData::class)->findOneBy(['recordIdentifier' => $path]);

            $normalisedNode[] = [
                'dirname'     => $basename,
                'description' => $data?->getDescription() ?? '',
                'path'        => $path,
                'serverPath'  => $nodeData['name'],
                'files'       => $files,
                'children'    => $children,
                'isLocked'    => $isLocked,
            ];
        }

        return $normalisedNode;
    }

    /**
     * Iterates over provided nodes, returns those (formatted) that are of type: file
     *
     * @param array  $nodes
     * @param string $dirPathHuman
     *
     * @return array
     */
    private function getDirNodeFiles(array $nodes, string $dirPathHuman): array
    {
        $files = [];
        foreach ($nodes as $nodeData) {
            $isFileNode = ($nodeData['type'] === 'file');
            if (!$isFileNode) {
                continue;
            }

            $filePath = Env::getUploadDir() . $dirPathHuman . DIRECTORY_SEPARATOR . basename($nodeData['name']);
            $fileTags = $this->em->getRepository(FilesTags::class)->getFileTagsEntityByFileFullPath($filePath);

            $basename = basename($nodeData['name']);

            $fileName = pathinfo($basename, PATHINFO_FILENAME);
            $fileExt  = pathinfo($basename, PATHINFO_EXTENSION);
            if (str_starts_with($basename, ".")) {
                $fileExt  = '';
                $fileName = $basename;
            }

            $files[]  = [
                'dir'  => Env::getUploadDir() . $dirPathHuman,
                'name' => $fileName,
                'ext'  => $fileExt,
                'size' => $nodeData['size'],
                'tags' => !is_null($fileTags) ? json_decode($fileTags->getTags(), true) : [],
            ];
        }

        return $files;
    }

}