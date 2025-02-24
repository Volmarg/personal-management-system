<?php

namespace App\Services\Module\Storage;

use App\Controller\Core\Env;
use App\Entity\FilesTags;
use App\Enum\StorageModuleEnum;
use App\Services\Files\PathService;
use App\Services\Shell\ShellTreeService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

class StorageService
{
    public function __construct(
        private readonly KernelInterface        $kernel,
        private readonly ShellTreeService       $shellTreeService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Recursively walks over the directory tree, building the frontend ready tree
     *
     * @param StorageModuleEnum $module
     *
     * @return array
     *
     * @throws Exception
     */
    public function getTreeFrontendData(StorageModuleEnum $module): array
    {
        $basePath    = PathService::getStorageModuleBaseDir($module);
        $scannedPath = PathService::setTrailingDirSeparator($this->kernel->getProjectDir()) . $basePath;

        $json    = $this->shellTreeService->getDirJsonTree($scannedPath);
        $mainArr = json_decode($json, true);

        return $this->traverseContent($mainArr);
    }

    /**
     * Recursively walks over the directory tree
     *
     * @param array $nodes
     *
     * @return array
     */
    private function traverseContent(array $nodes): array
    {
        $normalisedNode = [];
        foreach ($nodes as $nodeData) {
            $isDirNode = ($nodeData['type'] === 'directory');
            if (!$isDirNode) {
                continue;
            }

            $children = [];
            if (array_key_exists('contents', $nodeData)) {
                $children = $this->traverseContent($nodeData['contents'] ?? []);
            }

            $humanPath = preg_replace("#.*" . Env::getUploadDir() . "#", "", $nodeData['name']);
            $files     = $this->getDirNodeFiles($nodeData['contents'] ?? [], $humanPath);
            $basename  = basename($nodeData['name']);
            $normalisedNode[] = [
                'dirname'    => $basename,
                'path'       => Env::getUploadDir() . $humanPath,
                'serverPath' => $nodeData['name'],
                'files'      => $files,
                'children'   => $children,
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
            $files[]  = [
                'name' => pathinfo($basename, PATHINFO_FILENAME),
                'ext'  => pathinfo($basename, PATHINFO_EXTENSION),
                'size' => $nodeData['size'],
                'tags' => !is_null($fileTags) ? json_decode($fileTags->getTags(), true) : [],
            ];
        }

        return $files;
    }

}