<?php

namespace App\Response\UploadedFile;

use App\Action\File\UploadedFileAction;
use App\Response\Base\BaseResponse;

/**
 * Response for {@see UploadedFileAction::upload()}
 */
class UploadResponse extends BaseResponse
{

    private string $status;
    private string $publicPath;
    private string $localFileName;
    private ?string $uploadId;

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getPublicPath(): string
    {
        return $this->publicPath;
    }

    /**
     * @param string $publicPath
     */
    public function setPublicPath(string $publicPath): void
    {
        $this->publicPath = $publicPath;
    }

    /**
     * @return string
     */
    public function getLocalFileName(): string
    {
        return $this->localFileName;
    }

    /**
     * @param string $localFileName
     */
    public function setLocalFileName(string $localFileName): void
    {
        $this->localFileName = $localFileName;
    }

    /**
     * @return string|null
     */
    public function getUploadId(): ?string
    {
        return $this->uploadId;
    }

    /**
     * @param string|null $uploadId
     */
    public function setUploadId(?string $uploadId): void
    {
        $this->uploadId = $uploadId;
    }

}