<?php

namespace App\Response\UploadedFile;

use App\DTO\Internal\Upload\UploadConfigurationDTO;
use App\Response\Base\BaseResponse;

class UploadConfigurationResponse extends BaseResponse
{
    /**
     * @var UploadConfigurationDTO $configuration
     */
    private UploadConfigurationDTO $configuration;

    /**
     * @return UploadConfigurationDTO
     */
    public function getConfiguration(): UploadConfigurationDTO
    {
        return $this->configuration;
    }

    /**
     * @param UploadConfigurationDTO $configuration
     */
    public function setConfiguration(UploadConfigurationDTO $configuration): void
    {
        $this->configuration = $configuration;
    }

}