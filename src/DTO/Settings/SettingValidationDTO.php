<?php

namespace App\DTO\Settings;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;
use App\DTO\Settings\Dashboard\SettingsWidgetSettingsDTO;

class SettingValidationDTO extends AbstractDTO{

    /**
     * @var bool $is_valid
     */
    private $is_valid = false;

    /**
     * @var string $message
     */
    private $message = '';

    /**
     * @return bool
     */
    public function isValid(): bool {
        return $this->is_valid;
    }

    /**
     * @param bool $is_valid
     */
    public function setIsValid(bool $is_valid): void {
        $this->is_valid = $is_valid;
    }

    /**
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void {
        $this->message = $message;
    }

}