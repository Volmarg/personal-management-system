<?php

namespace App\DTO\Internal\Upload;

/**
 * Upload configuration defines how the upload work on front and what kind of validations will be performed
 * both on front and back
 */
class UploadConfigurationDTO
{
    public function __construct(
        private readonly string $identifier,
        private readonly ?float $maxFileSizeMb,
        private readonly bool   $multiUpload,
        private readonly bool   $allowNaming,
        private readonly string $source,
        private ?string         $uploadDir,
        private readonly bool   $allowTagging,
        private readonly array  $allowedExtensions = [],
        private readonly array  $allowedMimeTypes = [],
    ) {
    }

    /**
     * @return float|null
     */
    public function getMaxFileSizeMb(): ?float
    {
        return $this->maxFileSizeMb;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
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
     * @return null|string
     */
    public function getUploadDir(): ?string
    {
        return $this->uploadDir;
    }

    /**
     * @param string|null $uploadDir
     *
     * @return void
     */
    public function setUploadDir(?string $uploadDir): void
    {
        $this->uploadDir = $uploadDir;
    }

    /**
     * @return bool
     */
    public function isAllowTagging(): bool
    {
        return $this->allowTagging;
    }

}