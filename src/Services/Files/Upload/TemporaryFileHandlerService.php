<?php

namespace App\Services\Files\Upload;

use LogicException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handles all the logic related to saving files to temporary dir:
 * - for upload use explicitly {@see FileUploadService} as the uploaded files must be validated
 */
class TemporaryFileHandlerService
{

    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    ){}

    /**
     * It's basically taking file content and saving it as some random temporary file
     *
     * @param string $fileContent
     * @param string $originalFileName
     *
     * @return UploadedFile
     */
    public function saveFile(string $fileContent, string $originalFileName): UploadedFile
    {
        $extension           = preg_replace("#(.*)\.(.*)#", "$2", $originalFileName) ?? "";
        $fileName            = uniqid("temporary_upload_") . ".{$extension}";
        $tmpDirFolder        = $this->parameterBag->get('upload.tmp.dir');
        $tmpFileAbsolutePath = $tmpDirFolder . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($tmpDirFolder)) {
            mkdir($tmpDirFolder);
        }

        $this->validateTmpDir($tmpDirFolder);
        $result = @file_put_contents($tmpFileAbsolutePath, $fileContent);
        if (is_bool($result)) {
            $possibleError = json_encode(error_get_last(), JSON_PRETTY_PRINT);
            throw new LogicException("Failed saving the file under path: {$tmpFileAbsolutePath}. Maybe related error: {$possibleError}");
        }

        $this->validateTmpFile($tmpFileAbsolutePath);

        return new UploadedFile($tmpFileAbsolutePath, $fileName);
    }

    /**
     * @param string $tmpDirFolder
     */
    private function validateTmpDir(string $tmpDirFolder): void
    {
        if (!file_exists($tmpDirFolder)) {
            throw new LogicException("Temp dir does not exist: {$tmpDirFolder}");
        }

        if (!is_dir($tmpDirFolder)) {
            throw new LogicException("Temp dir is actually not a folder: {$tmpDirFolder}");
        }

        if (!is_writable($tmpDirFolder)) {
            throw new LogicException("Temp dir is not writable: {$tmpDirFolder}");
        }
    }

    /**
     * @param string $tmpFileAbsolutePath
     */
    private function validateTmpFile(string $tmpFileAbsolutePath): void
    {
        if (!file_exists($tmpFileAbsolutePath)) {
            throw new LogicException("Temp file does not exist under path: {$tmpFileAbsolutePath}");
        }
    }

}