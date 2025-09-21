<?php

namespace App\DTO\Settings\Notifications;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;
use App\Services\Exceptions\ExceptionValueNotAllowed;
use Exception;

class ConfigDTO extends AbstractDTO implements dtoInterface
{
    public const KEY_NAME                = 'name';
    public const KEY_VALUE               = 'value';
    public const KEY_ACTIVE_FOR_REMINDER = 'activeForReminder';

    private string $value;
    private string $name;
    private bool   $activeForReminder = false;

    public function isActiveForReminder(): bool
    {
        return $this->activeForReminder;
    }

    public function setActiveForReminder(bool $activeForReminder): void
    {
        $this->activeForReminder = $activeForReminder;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (empty($name)) {
            throw new ExceptionValueNotAllowed(ExceptionValueNotAllowed::KEY_MODE_STRING_NOT_EMPTY);
        }

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

    /**
     * @param string $json
     *
     * @return ConfigDTO
     * @throws Exception
     */
    public static function fromJson(string $json): self
    {
        $array = json_decode($json, true);

        $name              = self::checkAndGetKey($array, self::KEY_NAME);
        $value             = self::checkAndGetKey($array, self::KEY_VALUE);
        $activeForReminder = self::checkAndGetKey($array, self::KEY_ACTIVE_FOR_REMINDER);

        $dto = new self();
        $dto->setName($name);
        $dto->setValue($value);
        $dto->setActiveForReminder($activeForReminder);

        return $dto;
    }

    /**
     * @return string
     */
    public function toJson(): string
    {

        $array = $this->toArray();
        $json  = json_encode($array);

        return $json;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::KEY_NAME                => $this->getName(),
            self::KEY_VALUE               => $this->getValue(),
            self::KEY_ACTIVE_FOR_REMINDER => $this->isActiveForReminder(),
        ];
    }

}