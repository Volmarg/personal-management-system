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
    private $widgetSettings = [];

    /**
     * @return SettingsWidgetSettingsDTO
     */
    public function getWidgetSettings(): SettingsWidgetSettingsDTO {
        return $this->widgetSettings;
    }

    /**
     * @param SettingsWidgetSettingsDTO $widgetSettingsDto
     */
    public function setWidgetSettings(SettingsWidgetSettingsDTO $widgetSettingsDto): void {
        $this->widgetSettings = $widgetSettingsDto;
    }


    /**
     * @param string $settingsDashboardJson
     * @return SettingsDashboardDTO
     * @throws \Exception
     */
    public static function fromJson(string $settingsDashboardJson): self{
        $settingsDashboardArray = \GuzzleHttp\json_decode($settingsDashboardJson, true);
        $widgetsSettingsJson    = self::checkAndGetKey($settingsDashboardArray, self::KEY_WIDGETS_SETTINGS);

        $settingsDashboardDto = new self();
        $settingsDashboardDto->setWidgetSettings(
            SettingsWidgetSettingsDTO::fromJson($widgetsSettingsJson)
        );

        return $settingsDashboardDto;
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