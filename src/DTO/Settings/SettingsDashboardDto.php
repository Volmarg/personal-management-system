<?php

namespace App\DTO\Settings;

use App\DTO\AbstractDTO;
use App\DTO\DtoInterface;
use App\DTO\Settings\Dashboard\SettingsWidgetSettingsDto;

class SettingsDashboardDto extends AbstractDTO implements DtoInterface{

    const KEY_WIDGETS_SETTINGS = 'widgets_settings';

    /**
     * @var SettingsWidgetSettingsDto
     */
    private $widgetSettings = [];

    /**
     * @return SettingsWidgetSettingsDto
     */
    public function getWidgetSettings(): SettingsWidgetSettingsDto {
        return $this->widgetSettings;
    }

    /**
     * @param SettingsWidgetSettingsDto $widgetSettingsDto
     */
    public function setWidgetSettings(SettingsWidgetSettingsDto $widgetSettingsDto): void {
        $this->widgetSettings = $widgetSettingsDto;
    }


    /**
     * @param string $settingsDashboardJson
     *
     * @return SettingsDashboardDto
     * @throws \Exception
     */
    public static function fromJson(string $settingsDashboardJson): self{
        $settingsDashboardArray = \GuzzleHttp\json_decode($settingsDashboardJson, true);
        $widgetsSettingsJson    = self::checkAndGetKey($settingsDashboardArray, self::KEY_WIDGETS_SETTINGS);

        $settingsDashboardDto = new self();
        $settingsDashboardDto->setWidgetSettings(
            SettingsWidgetSettingsDto::fromJson($widgetsSettingsJson)
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