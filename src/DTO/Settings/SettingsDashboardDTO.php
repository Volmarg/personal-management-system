<?php

namespace App\DTO\Settings;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;
use App\DTO\Settings\Dashboard\SettingsWidgetSettingsDTO;

class SettingsDashboardDTO extends AbstractDTO implements dtoInterface{

    const KEY_WIDGETS_SETTINGS = 'widgets_settings';

    /**
     * @var SettingsWidgetSettingsDTO
     */
    private $widget_settings = [];

    /**
     * @return SettingsWidgetSettingsDTO
     */
    public function getWidgetSettings(): SettingsWidgetSettingsDTO {
        return $this->widget_settings;
    }

    /**
     * @param SettingsWidgetSettingsDTO $widget_settings_dto
     */
    public function setWidgetSettings(SettingsWidgetSettingsDTO $widget_settings_dto): void {
        $this->widget_settings = $widget_settings_dto;
    }


    /**
     * @param string $settings_dashboard_json
     * @return SettingsDashboardDTO
     * @throws \Exception
     */
    public static function fromJson(string $settings_dashboard_json): self{
        $settings_dashboard_array   = \GuzzleHttp\json_decode($settings_dashboard_json, true);
        $widgets_settings_json      = self::checkAndGetKey($settings_dashboard_array, self::KEY_WIDGETS_SETTINGS);

        $settings_dashboard_dto = new self();

        $settings_dashboard_dto->setWidgetSettings(
            SettingsWidgetSettingsDTO::fromJson($widgets_settings_json)
        );

        return $settings_dashboard_dto;
    }

    /**
     * @return string
     */
    public function toJson(): string{

        $array = [
            self::KEY_WIDGETS_SETTINGS => $this->getWidgetSettings()->toArray(),
        ];

        $json = json_encode($array);

        return $json;
    }

}