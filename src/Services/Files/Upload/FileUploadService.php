<?php

namespace App\Services\Files\Upload;

use App\Enum\File\UploadedFileSourceEnum;
use App\Exception\File\UploadValidationException;
use App\Services\Core\Logger;
use App\Services\Files\PathService;
use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use TypeError;

/**
 * Handles uploading files,
 * Based on the:
 * -{@link https://symfony.com/doc/current/controller/upload_file.html}
 */
readonly class FileUploadService
{
    /**
     * @param Logger                 $logger
     * @param FileUploadValidator    $fileUploadValidator
     * @param FileUploadConfigurator $fileUploadConfigurator
     */
    public function __construct(
        private Logger                 $logger,
        private FileUploadValidator    $fileUploadValidator,
        private FileUploadConfigurator $fileUploadConfigurator,
    ) {
    }

    /**
     * Will validate the uploaded file and save it in proper target directory
     *
     * @param UploadedFile $tmpFile
     * @param string       $uploadConfigId
     * @param string       $fileName
     * @param float        $frontendFileSizeBytes
     *
     * @return string
     *
     * @throws UploadValidationException
     * @throws Exception
     */
    public function handleUpload(UploadedFile $tmpFile, string $uploadConfigId, string $fileName, float $frontendFileSizeBytes): string
    {
        try {
            $uploadConfiguration = $this->fileUploadConfigurator->getConfiguration($uploadConfigId);
            $fileSourceEnum      = UploadedFileSourceEnum::tryFrom($uploadConfiguration->getSource());

            $this->fileUploadValidator->init($tmpFile, $frontendFileSizeBytes);
            $this->fileUploadValidator->preUploadValidation($tmpFile, $uploadConfiguration);

            $uploadDirPath = $this->buildUploadDirectoryPath($fileSourceEnum);

            $this->createUploadDirectory($uploadDirPath);

            $targetPath = $this->decideTargetPath($uploadDirPath, $fileName);
            $this->moveFromTemp($tmpFile->getPathname(), $targetPath);

            $this->fileUploadValidator->postMoveValidation($targetPath);
        } catch (FileException | UploadValidationException $e) {
            if (file_exists($tmpFile->getPathname())) {
                unlink($tmpFile->getPathname());
            }

            $this->logger->logException($e);

            throw $e;
        }

        return $targetPath;
    }

    /**
     * Handles file removal
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function deleteFile(string $filePath): bool
    {
        $fileExists = file_exists($filePath);
        if ($fileExists) {
            $fileContent = file_get_contents($filePath);
        }

        try {
            $isRemoved = @unlink($filePath);
            if (!$isRemoved) {
                $this->logger->getLogger()->critical("Could not remove uploaded file for path: {$filePath}, reason: " . error_get_last());
                return false;
            }
        } catch (Exception|TypeError $e) {
            $info = [
                "info" => "Could not remove file for path: {$filePath}",
            ];

            if (isset($isRemoved) && isset($fileContent)) {
                $isReverted = @file_put_contents($filePath, $fileContent);
                if (!$isReverted) {
                    $this->logger->getLogger()->critical("Tried to revert the removed file, but could not do that, something went wrong with reversing", [
                        "possibleIssue" => error_get_last(),
                        "info"          => "Keep in mind that this error might be totally unrelated, unknown if file put content errors are caught by it"
                    ]);
                }
            }

            $this->logger->logException($e, $info);

            return false;
        }

        return true;
    }

    /**
     * Builds the path to the folder in which file will be saved to
     *
     * @param UploadedFileSourceEnum $fileSourceEnum
     *
     * @return string
     */
    private function buildUploadDirectoryPath(UploadedFileSourceEnum $fileSourceEnum): string
    {
        return match ($fileSourceEnum->value) {
            UploadedFileSourceEnum::PROFILE_IMAGE->value => PathService::getProfileImageUploadDir(),
            default => PathService::getUploadDir(),
        };
    }

    /**
     * @param string $uploadDirPath
     * @param string $fileName
     *
     * @return string
     * @throws Exception
     */
    private function decideTargetPath(string $uploadDirPath,string $fileName): string
    {
        $targetPath = PathService::setTrailingDirSeparator($uploadDirPath) . $fileName;
        if (file_exists($targetPath)) {
            $uuid           = Uuid::uuid4();
            $nameWithoutExt = pathinfo($targetPath, PATHINFO_FILENAME);
            $extension      = pathinfo($targetPath, PATHINFO_EXTENSION);
            $timestamp      = (new \DateTime())->format("Y-m-d_H-i-s");
            $targetPath     = PathService::setTrailingDirSeparator($uploadDirPath) . "{$nameWithoutExt}-{$timestamp}-{$uuid}.{$extension}";
        }

        return $targetPath;
    }

    /**
     * Will attempt to create the folder path used for current upload
     *
     * @param string $path
     */
    private function createUploadDirectory(string $path): void
    {
        if (file_exists($path)) {
            return;
        }

        $isSuccess = @mkdir($path, 0755, true);
        if (!$isSuccess) {
            throw new FileException("Could not create upload folder: {$path}. Error: " . json_encode(error_get_last()));
        }
    }

    /**
     * Moves the file from temp path to target path
     *
     * @param string $tempPath
     * @param string $targetPath
     * @throws Exception
     */
    private function moveFromTemp(string $tempPath, string $targetPath): void
    {
        $isMoved = rename($tempPath, $targetPath);
        if (!$isMoved) {
            $lastError = json_encode(error_get_last(), JSON_PRETTY_PRINT);
            $message = "
                        Could not move the file from temp path: {$tempPath} to target folder ($targetPath).
                        Got error {$lastError}
                    ";
            throw new Exception($message);
        }
    }

}
