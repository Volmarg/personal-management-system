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
    private $is_visible = false;

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
        return $this->is_visible;
    }

    /**
     * @param bool $is_visible
     */
    public function setIsVisible(bool $is_visible): void {
        $this->is_visible = $is_visible;
    }

    /**
     * @param string $widget_visibility_json
     * @return SettingsWidgetVisibilityDTO
     * @throws \Exception
     */
    public static function fromJson(string $widget_visibility_json): self{
        $widget_visibility_array = \GuzzleHttp\json_decode($widget_visibility_json, true);

        $name       = self::checkAndGetKey($widget_visibility_array, self::KEY_NAME);
        $is_visible = self::checkAndGetKey($widget_visibility_array, self::KEY_IS_VISIBLE);

        $settings_widget_visibility_dto = new self();
        $settings_widget_visibility_dto->setName($name);
        $settings_widget_visibility_dto->setIsVisible($is_visible);

        return $settings_widget_visibility_dto;

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