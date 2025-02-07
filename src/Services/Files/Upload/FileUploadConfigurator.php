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
            self::PROFILE_PICTURE_UPLOAD  => $this->buildProfilePictureUploadConfig(),
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
            maxFileSizeMb: 0.5,
            multiUpload: false,
            allowNaming: false,
            source: UploadedFileSourceEnum::PROFILE_IMAGE->value,
            uploadDir: PathService::getUploadDir(),
            allowedExtensions: ["jpg", "png", "jpeg" ],
            allowedMimeTypes: ["image/png", "image/jpg", "image/jpeg"]
        );
    }
}