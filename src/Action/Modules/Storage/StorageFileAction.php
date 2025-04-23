<?php

namespace App\Action\Modules\Storage;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Entity\FilesTags;
use App\Response\Base\BaseResponse;
use App\Services\Core\Logger;
use App\Services\Module\Storage\StorageFileService;
use App\Services\Module\Storage\StorageService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/storage/file", name: "module.storage.file.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_STORAGE])]
class StorageFileAction extends AbstractController
{
    public function __construct(
        private readonly StorageService         $storageService,
        private readonly StorageFileService     $storageFileService,
        private readonly Logger                 $logger,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface    $translator
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route("/update", name: "rename", methods: [Request::METHOD_POST])]
    public function update(Request $request): JsonResponse
    {
        $dataArray    = RequestService::tryFromJsonBody($request);
        $currFileName = ArrayHandler::get($dataArray, 'currFileName', allowEmpty: false);
        $newFileName  = ArrayHandler::get($dataArray, 'newFileName', allowEmpty: false);
        $dirPath      = ArrayHandler::get($dataArray, 'dirPath', allowEmpty: false);
        $tags         = ArrayHandler::get($dataArray, 'tags');

        $oldPath = $dirPath . DIRECTORY_SEPARATOR . $currFileName;
        $newPath = $dirPath . DIRECTORY_SEPARATOR . $newFileName;

        $response = $this->storageFileService->validatePreUpdate($newPath, $oldPath, $dirPath, $currFileName, $newFileName);
        if ($response instanceof BaseResponse) {
            return $response->toJsonResponse();
        }

        $isRenamed = rename($oldPath, $newPath);
        if (!$isRenamed) {
            $msg = $this->translator->trans('module.storage.common.unknownError');
            $this->logger->getLogger()->critical($msg, [
                'info'          => "rename function failed",
                'oldPath'       => $oldPath,
                'newPath'       => $newPath,
                'possibleError' => error_get_last(),
            ]);
            return BaseResponse::buildOkResponse($msg)->toJsonResponse();
        }

        $tagsEntity = $this->em->getRepository(FilesTags::class)->getFileTagsEntityByFileFullPath($oldPath);
        if (is_null($tagsEntity)) {
            $tagsEntity = new FilesTags();
        }

        $tagsEntity->setTags(json_encode($tags));
        $tagsEntity->setFullFilePath($newPath);

        $this->em->persist($tagsEntity);
        $this->em->flush();

        $msg = $this->translator->trans('module.storage.common.fileHasBeenUpdated');
        return BaseResponse::buildOkResponse($msg)->toJsonResponse();
    }

    /**
     * Handles files removal
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route("/remove", name: "remove", methods: [Request::METHOD_POST])]
    public function removeFile(Request $request): JsonResponse
    {
        $dataArray = RequestService::tryFromJsonBody($request);
        $fileNames = ArrayHandler::get($dataArray, 'fileNames');
        $dirPath   = ArrayHandler::get($dataArray, 'dirPath');

        $this->storageService->ensureStorageManipulation($dirPath);

        $filePaths = [];
        foreach ($fileNames as $fileName) {
            $filePaths[] = $dirPath . DIRECTORY_SEPARATOR . $fileName;
        }

        $notRemovedPaths = $this->storageFileService->removeFiles($filePaths);
        if (!empty($notRemovedPaths)) {
            $msg = $this->translator->trans('module.storage.remove.couldNotRemoveSomeFiles') . ": " . json_encode($notRemovedPaths);
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        $msg = $this->translator->trans('module.storage.remove.filesHaveBeenRemoved');
        return BaseResponse::buildOkResponse($msg)->toJsonResponse();
    }

    /**
     * @throws Exception
     */
    #[Route("/move", name: "move", methods: [Request::METHOD_POST])]
    public function moveFiles(Request $request): JsonResponse
    {
        $dataArray   = RequestService::tryFromJsonBody($request);
        $currDirPath = ArrayHandler::get($dataArray, 'currDirPath', allowEmpty: false);
        $newDirPath  = ArrayHandler::get($dataArray, 'newDirPath', allowEmpty: false);
        $filesNames  = ArrayHandler::get($dataArray, 'fileNames', allowEmpty: false);

        $this->storageService->ensureStorageManipulation($currDirPath);
        $this->storageService->ensureStorageManipulation($newDirPath);

        if (empty($filesNames)) {
            $msg = $this->translator->trans('module.storage.moveFileOrDir.filesListEmpty');
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        $response = $this->storageService->moveFiles($currDirPath, $newDirPath, $filesNames);

        return $response->toJsonResponse();
    }

}