<?php

namespace App\Action\Modules\System;

use App\Attribute\ModuleAttribute;
use App\Entity\FilesTags;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\System\LockedResource;
use App\Enum\StorageModuleEnum;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\Module\Storage\StorageService;
use App\Services\System\LockedResourceService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/system/search", name: "module.system.search.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_SYSTEM])]
class SearchAction extends AbstractController {

    public function __construct(
        private readonly StorageService $storageService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LockedResourceService $lockedResourceService
    ) {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(Request $request): JsonResponse
    {
        $query = $request->query->get('query');
        if (empty($query)) {
            throw new LogicException("Query cannot be empty");
        }

        $entriesData = [];
        $entriesData = $this->searchInStorage($query, $entriesData);
        $entriesData = $this->searchInNotes($query, $entriesData);

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param string $query
     * @param array  $entriesData
     *
     * @return array[]
     * @throws Exception
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function searchInStorage(string $query, array $entriesData): array
    {
        $queryParts     = explode(" ", $query);
        $cartesianQuery = [];
        ArrayHandler::cartesianProduct(input: $queryParts, result: $cartesianQuery, depth: 2, stringBetween: " ");

        $queryTagMatchSet = array_merge($queryParts, $cartesianQuery);
        foreach (StorageModuleEnum::cases() as $enum) {
            $matchingFiles = [];
            $files         = [];

            $this->storageService->getTreeData($enum, $files);
            foreach ($files as $fileData) {
                $fileName = $fileData['name'] . (!empty($fileData['ext']) ? '.' . $fileData['ext'] : '');

                $fullPath  = $fileData['dir'] . DIRECTORY_SEPARATOR . $fileName;
                $tagEntity = $this->entityManager->getRepository(FilesTags::class)->getFileTagsEntityByFileFullPath($fullPath);
                $hasTag    = false;

                if (!is_null($tagEntity)) {
                    $hasTag = $tagEntity->isAnyTagMatching($queryTagMatchSet);
                }

                if (!str_contains(strtolower($fileName), strtolower($query)) && !$hasTag) {
                    continue;
                }

                $moduleName = ModulesService::getUploadModuleNameByFilePath($fileData['dir']);
                if ($this->lockedResourceService->isResourceLocked('', LockedResource::TYPE_MODULE, $moduleName)) {
                    continue;
                }

                $matchingFiles[] = [
                    'name'        => $fileName,
                    'identifiers' => [
                        basename($fileData['dir']),
                        $fileData['dir'],
                    ],
                ];
            }

            $entriesData[$enum->value] = $matchingFiles;
        }

        return $entriesData;
    }

    /**
     * @param string $query
     * @param array  $entriesData
     *
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function searchInNotes(string $query, array $entriesData): array
    {
        if ($this->lockedResourceService->isResourceLocked('', LockedResource::TYPE_MODULE, ModulesService::MODULE_NAME_NOTES)) {
            return [];
        }

        $notes = $this->entityManager->getRepository(MyNotes::class)->findByTitle($query);

        $notesData = [];
        foreach ($notes as $note) {
            $notesData[] = [
                'name'        => $note->getTitle(),
                'identifiers' => [
                    $note->getCategory()?->getId(),
                ],
            ];
        }

        $entriesData['notes'] = $notesData;

        return $entriesData;
    }

}