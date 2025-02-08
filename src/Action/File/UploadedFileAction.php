<?php

namespace App\Action\File;

use App\Services\Core\Logger;
use App\Services\Files\PathService;
use App\Services\Files\Upload\TemporaryFileHandlerService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use App\DTO\Internal\Upload\UploadConfigurationDTO;
use App\Enum\File\UploadStatusEnum;
use App\Exception\File\UploadValidationException;
use App\Response\Base\BaseResponse;
use App\Response\UploadedFile\UploadConfigurationResponse;
use App\Response\UploadedFile\UploadResponse;
use App\Services\Files\Upload\FileUploadConfigurator;
use App\Services\Files\Upload\FileUploadService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use TypeError;

/**
 * Provides endpoints for uploaded file related logic
 */
class UploadedFileAction extends AbstractController
{

    public function __construct(
        private readonly FileUploadService           $fileUploadService,
        private readonly Logger                      $loggerService,
        private readonly FileUploadConfigurator      $fileUploadConfigurator,
        private readonly TranslatorInterface         $translator,
        private readonly TemporaryFileHandlerService $temporaryFileHandlerService
    ) {
    }

    /**
     * Handles the file upload.
     * Keep in mind that trailing slash in route is A MUST, had already issues with that.
     *
     * Info: upload won't work if the route is set to "/upload/"
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/upload/send/", name: "upload.send", methods: [Request::METHOD_POST, Request::METHOD_OPTIONS])]
    public function upload(Request $request): JsonResponse
    {
        $dataArray       = RequestService::tryFromJsonBody($request);
        $encodedContent  = ArrayHandler::get($dataArray, 'fileContent');
        $fileName        = ArrayHandler::get($dataArray, 'fileName');
        $fileSizeBytes   = ArrayHandler::get($dataArray, 'fileSize');
        $uploadConfigId  = ArrayHandler::get($dataArray, 'uploadConfigId');
        $userDefinedName = ArrayHandler::get($dataArray, 'userDefinedName', true);
        $tags            = ArrayHandler::get($dataArray, 'tags', true, []);
        // todo: extend with tags needed later in storage

        $decodedFileContent = base64_decode($encodedContent);

        $errorMessage = $this->translator->trans('file.upload.msg.uploadError');
        $isError      = false;
        $status       = UploadStatusEnum::SUCCESS;
        $response     = UploadResponse::buildOkResponse();

        try {
            $usedFileName = $fileName;
            if (!empty($userDefinedName)) {
                $usedFileName = $userDefinedName . "." . pathinfo($fileName, PATHINFO_EXTENSION);
            }

            $uploadedTmpFile = $this->temporaryFileHandlerService->saveFile($decodedFileContent, $fileName);
            $filePath = $this->fileUploadService->handleUpload($uploadedTmpFile, $uploadConfigId, $usedFileName, $fileSizeBytes);

            $response->setPublicPath($filePath);
            $response->setLocalFileName($usedFileName);
            $response->setUploadId($uploadConfigId);
            $response->setMessage($this->translator->trans('file.upload.msg.uploadSuccess'));
        } catch (UploadValidationException) {
            $status  = UploadStatusEnum::ERROR;
            $isError = true;
        } catch (Exception|TypeError $e) {
            $status = UploadStatusEnum::ERROR;
            $this->loggerService->logException($e);
            $isError = true;
        }

        if ($isError) {
            $response->prefillBaseFieldsForBadRequestResponse();
            $response->setMessage($errorMessage);
            if (isset($uploadedTmpFile)) {
                unlink($uploadedTmpFile->getRealPath());
            }
        }

        $response->setStatus($status->value);

        return $response->toJsonResponse();
    }

    /**
     * Returns the serialized {@see UploadConfigurationDTO} from {@see FileUploadConfigurator}
     *
     * @param string $id
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/upload/get-configuration/{id}", name: "upload.get.configuration", methods: [Request::METHOD_GET, Request::METHOD_OPTIONS])]
    public function getUploadConfiguration(string $id): JsonResponse
    {
        $configurationDto = $this->fileUploadConfigurator->getConfiguration($id);
        $response         = UploadConfigurationResponse::buildOkResponse();
        $response->setConfiguration($configurationDto);

        return $response->toJsonResponse();
    }

    /**
     * Will update the give file data
     *
     * @param string  $filePath
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route("/upload/update/{filePath}", name: "upload.update", methods: [Request::METHOD_POST, Request::METHOD_OPTIONS])]
    public function update(string $filePath, Request $request): JsonResponse
    {
        //todo: update the file now. tags etc.
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * Will remove given file
     *
     * @param string $filePath
     *
     * @return JsonResponse
     */
    #[Route("/upload/delete/{filePath}", name: "upload.delete", methods: [Request::METHOD_GET, Request::METHOD_OPTIONS])]
    public function delete(string $filePath): JsonResponse
    {
        if (!file_exists($filePath)) {
            $msg = $this->translator->trans('file.msg.fileDoesNotExist');
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        try {
            PathService::validatePathSafety($filePath);
        } catch (Throwable) {
            $msg = $this->translator->trans('file.msg.unsafePathCancelled');
            return BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        }

        $message   = $this->translator->trans('file.msg.fileRemoved');
        $response  = BaseResponse::buildOkResponse();
        $response->setMessage($message);

        $isRemoved = $this->fileUploadService->deleteFile($filePath);
        if (!$isRemoved) {
            $message = $this->translator->trans('file.msg.couldNotRemove');
            $response->prefillBaseFieldsForBadRequestResponse();
            $response->setMessage($message);
        }

        return $response->toJsonResponse();
    }

}