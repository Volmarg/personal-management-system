<?php

namespace App\DTO\Modules\Health;

use App\Traits\SerializerAwareTrait;

class DoctorContactDto {

    use SerializerAwareTrait;

    public function __construct(
        private string $name = "",
        private string $value = "",
        /**
         * This value is later used on front to identify which contact type in json we change as one contact may have few phone numbers etc
         */
        private string $uuid = "",
    ) {

    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

}