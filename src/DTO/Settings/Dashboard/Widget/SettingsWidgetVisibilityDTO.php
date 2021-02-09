<?php

namespace App\DTO\Settings\Dashboard\Widget;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;

class SettingsWidgetVisibilityDTO extends AbstractDTO implements dtoInterface{

    const KEY_NAME       = 'name';
    const KEY_IS_VISIBLE = 'is_visible';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var bool
     */
    private $isVisible = false;

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool {
        return $this->isVisible;
    }

    /**
     * @param bool $isVisible
     */
    public function setIsVisible(bool $isVisible): void {
        $this->isVisible = $isVisible;
    }

    /**
     * @param string $widgetVisibilityJson
     * @return SettingsWidgetVisibilityDTO
     * @throws \Exception
     */
    public static function fromJson(string $widgetVisibilityJson): self{
        $widgetVisibilityArray = \GuzzleHttp\json_decode($widgetVisibilityJson, true);

        $name      = self::checkAndGetKey($widgetVisibilityArray, self::KEY_NAME);
        $IsVisible = self::checkAndGetKey($widgetVisibilityArray, self::KEY_IS_VISIBLE);

        $settingsWidgetVisibilityDto = new self();
        $settingsWidgetVisibilityDto->setName($name);
        $settingsWidgetVisibilityDto->setIsVisible($IsVisible);

        return $settingsWidgetVisibilityDto;

    }

    /**
     * @return string
     */
    public function toJson(): string{

        $array = $this->toArray();
        $json  = json_encode($array);

        return $json;
    }

    public function toArray(): array{

        $array = [
            self::KEY_NAME       => $this->getName(),
            self::KEY_IS_VISIBLE => $this->isVisible(),
        ];

        return $array;
    }

}