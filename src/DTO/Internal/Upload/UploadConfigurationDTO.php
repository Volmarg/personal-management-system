<?php

namespace App\DTO\Internal\Upload;

/**
 * Upload configuration defines how the upload work on front and what kind of validations will be performed
 * both on front and back
 */
readonly class UploadConfigurationDTO
{
    public function __construct(
        private string $identifier,
        private float  $maxFileSizeMb,
        private bool   $multiUpload,
        private bool   $allowNaming,
        private string $source,
        private string $uploadDir,
        private array  $allowedExtensions = [],
        private array  $allowedMimeTypes = [],
    ) {
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return float
     */
    public function getMaxFileSizeMb(): float
    {
        return $this->maxFileSizeMb;
    }

    /**
     * @return bool
     */
    public function isMultiUpload(): bool
    {
        return $this->multiUpload;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * @return array
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return bool
     */
    public function isAllowNaming(): bool
    {
        return $this->allowNaming;
    }

    /**
     * @return string
     */
    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }

}