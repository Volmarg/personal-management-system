<?php

namespace App\DTO\Settings\Lock\Subsettings;

use App\DTO\AbstractDTO;
use Exception;

/**
 * Transfers base data regarding module lock state in setting panel
 *
 * Class SettingsModuleLockDTO
 * @package App\DTO\Settings\Lock\Subsettings
 */
class SettingsModuleLockDTO extends AbstractDTO
{
    const KEY_NAME      = "name";
    const KEY_IS_LOCKED = "isLocked";

    /**
     * @var string $name
     */
    private string $name;

    /**
     * @var bool $locked
     */
    private bool $locked;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function isLocked(): string
    {
        return $this->locked;
    }

    /**
     * @param string $locked
     */
    public function setLocked(string $locked): void
    {
        $this->locked = $locked;
    }

    /**
     * @param string $json
     * @return SettingsModuleLockDTO
     * @throws Exception
     */
    public static function fromJson(string $json): self
    {
        $widgetVisibilityArray = json_decode($json, true);

        $name      = self::checkAndGetKey($widgetVisibilityArray, self::KEY_NAME);
        $IsVisible = self::checkAndGetKey($widgetVisibilityArray, self::KEY_IS_LOCKED);

        $dto = new self();
        $dto->setName($name);
        $dto->setLocked($IsVisible);

        return $dto;
    }

    /**
     * Returns array representation of current dto
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
           self::KEY_NAME      => $this->getName(),
           self::KEY_IS_LOCKED => $this->isLocked(),
        ];
    }

}