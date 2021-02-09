<?php

namespace App\DTO\Settings;

use App\DTO\AbstractDTO;

class SettingValidationDTO extends AbstractDTO{

    /**
     * @var bool $isValid
     */
    private $isValid = false;

    /**
     * @var string $message
     */
    private $message = '';

    /**
     * @return bool
     */
    public function isValid(): bool {
        return $this->isValid;
    }

    /**
     * @param bool $isValid
     */
    public function setIsValid(bool $isValid): void {
        $this->isValid = $isValid;
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