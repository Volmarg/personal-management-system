<?php

namespace App\Services\Files\Upload;

use App\DTO\Internal\Upload\UploadConfigurationDTO;
use App\Enum\File\UploadedFileSourceEnum;
use App\Services\Files\PathService;
use Exception;

/**
 * Builds the configuration for file uploader (set of rules to be applied both on front and back)
 * It's easier to have the same set of rules defined on front and back in one place
 */
class FileUploadConfigurator
{
    /**
     * These ids must be kept in sync with front, if any will ever get changed then it has to be updated on front too
     * Not using any human friendly names to prevent manipulating it on front
     */
    private const PROFILE_PICTURE_UPLOAD  = "ef61b8bf828699f522861815c9ac8969";
    private const IMAGE_STORAGE__ID = "12ds23hs8sh7s4645678f4sadg456789as1";
    private const VIDEOS_STORAGE_ID = "312s456d7gewse123rg7gh456g1s";
    private const FILES_STORAGE_ID  = "645dsf789shkdczs23431245jk687sd123";
    private const MONTHLY_PAYMENTS_IMPORT_ID  = "b54c2e78d5dbcc4441f813da11783859e2588d8b";

    private const array SUPPORTED_IMAGE_EXTENSIONS = [
        "bm",
        "bmp",
        "gif",
        "ico",
        "jfif",
        "jfif-tbnl",
        "jpe",
        "jpeg",
        "jpg",
        "png",
        "tif",
        "wbmp",
        "web",
        "webp",
    ];

    private const array SUPPORTED_IMAGE_MIME_TYPES = [
        "image/bmp",
        "image/gif",
        "image/x-icon",
        "image/jpeg",
        "image/pjpeg",
        "image/png",
        "image/tiff",
        "image/x-tiff",
        "image/webp",
        "image/vnd.wap.wbmp",
    ];

    /**
     * Returns upload configuration for given identifier
     *
     * @param string $configurationId
     *
     * @return UploadConfigurationDTO
     * @throws Exception
     */
    public function getConfiguration(string $configurationId): UploadConfigurationDTO
    {
        $configuration =  match ($configurationId) {
            self::PROFILE_PICTURE_UPLOAD     => $this->buildProfilePictureUploadConfig(),
            self::IMAGE_STORAGE__ID          => $this->buildImageStorageConfig(),
            self::VIDEOS_STORAGE_ID          => $this->buildVideosStorageConfig(),
            self::FILES_STORAGE_ID           => $this->buildFilesStorageConfig(),
            self::MONTHLY_PAYMENTS_IMPORT_ID => $this->buildMonthlyPaymentsImportConfig(),
            default                       => throw new Exception("There is no configuration builder defined for identifier: {$configurationId}")
        };

        return $configuration;
    }

    /**
     * @return UploadConfigurationDTO
     */
    private function buildProfilePictureUploadConfig(): UploadConfigurationDTO
    {
        return new UploadConfigurationDTO(
            identifier: self::PROFILE_PICTURE_UPLOAD,
            maxFileSizeMb: 2,
            multiUpload: false,
            allowNaming: false,
            source: UploadedFileSourceEnum::PROFILE_IMAGE->value,
            uploadDir: PathService::getProfileImageUploadDir(),
            allowTagging: false,
            allowedExtensions: self::SUPPORTED_IMAGE_EXTENSIONS,
            allowedMimeTypes: self::SUPPORTED_IMAGE_MIME_TYPES,
        );
    }

    /**
     * @return UploadConfigurationDTO
     */
    private function buildImageStorageConfig(): UploadConfigurationDTO
    {
        return new UploadConfigurationDTO(
            identifier: self::IMAGE_STORAGE__ID,
            maxFileSizeMb: null,
            multiUpload: true,
            allowNaming: true,
            source: UploadedFileSourceEnum::STORAGE_VIDEO_MODULE->value,
            uploadDir: null,
            allowTagging: true,
            allowedExtensions: self::SUPPORTED_IMAGE_EXTENSIONS,
            allowedMimeTypes: self::SUPPORTED_IMAGE_MIME_TYPES,
        );
    }

    /**
     * @return UploadConfigurationDTO
     */
    private function buildFilesStorageConfig(): UploadConfigurationDTO
    {
        return new UploadConfigurationDTO(
            identifier: self::FILES_STORAGE_ID,
            maxFileSizeMb: null,
            multiUpload: true,
            allowNaming: true,
            source: UploadedFileSourceEnum::STORAGE_VIDEO_MODULE->value,
            uploadDir: null,
            allowTagging: true
        );
    }

    /**
     * This config will NOT be used in backend-based upload
     *
     * @return UploadConfigurationDTO
     */
    private function buildMonthlyPaymentsImportConfig(): UploadConfigurationDTO
    {
        return new UploadConfigurationDTO(
            identifier: self::MONTHLY_PAYMENTS_IMPORT_ID,
            maxFileSizeMb: null,
            multiUpload: false,
            allowNaming: false,
            source: '',
            uploadDir: null,
            allowTagging: true,
            allowedExtensions: [
                "xlsx",
            ],
            allowedMimeTypes: [
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            ]
        );
    }

    /**
     * @return UploadConfigurationDTO
     */
    private function buildVideosStorageConfig(): UploadConfigurationDTO
    {
        return new UploadConfigurationDTO(
            identifier: self::VIDEOS_STORAGE_ID,
            maxFileSizeMb: null,
            multiUpload: true,
            allowNaming: true,
            source: UploadedFileSourceEnum::STORAGE_VIDEO_MODULE->value,
            uploadDir: null,
            allowTagging: true,
            allowedExtensions: [
                "ogv",
                "mp4",
                "mov",
                "m4v",
                "mkv",
                "webm"
            ],
            allowedMimeTypes: [
                'video/ogg',
                'video/mp4',
                'video/x-matroska',
                'video/quicktime',
                'video/x-m4v',
                'video/webm',
            ]
        );
    }

}