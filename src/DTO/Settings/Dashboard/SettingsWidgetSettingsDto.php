<?php

namespace App\DTO\Settings\Dashboard;

use App\DTO\AbstractDTO;
use App\DTO\DtoInterface;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDto;

class SettingsWidgetSettingsDto extends AbstractDTO implements DtoInterface{

    const KEY_WIDGETS_VISIBILITY = 'widgets_visibility';

    /**
     * @var SettingsWidgetVisibilityDto[]
     */
    private $widgetsVisibility = [];

    /**
     * @return SettingsWidgetVisibilityDto[]
     */
    public function getWidgetsVisibility(): array {
        return $this->widgetsVisibility;
    }

    /**
     * @param SettingsWidgetVisibilityDto $widgetVisibility
     */
    public function addWidgetVisibility(SettingsWidgetVisibilityDto $widgetVisibility): void {
        array_push($this->widgetsVisibility, $widgetVisibility);
    }

    /**
     * @param array $widgetsVisibilityDtos
     * @throws \Exception
     */
    public function setWidgetVisibility(array $widgetsVisibilityDtos){

        $hasDto = reset($widgetsVisibilityDtos) instanceof SettingsWidgetVisibilityDto;

        if( !$hasDto ){
            throw new \Exception("There are no SettingsWidgetVisibilityDTO in array ");
        }

        $this->widgetsVisibility = $widgetsVisibilityDtos;
    }

    /**
     * @param string $widgetsSettingsJson
     *
     * @return SettingsWidgetSettingsDto
     * @throws \Exception
     */
    public static function fromJson(string $widgetsSettingsJson): self{
        $widgetsSettingsArray   = \GuzzleHttp\json_decode($widgetsSettingsJson, true);
        $widgetsVisibilityJson  = self::checkAndGetKey($widgetsSettingsArray, self::KEY_WIDGETS_VISIBILITY);
        $widgetVisibilityArrays = \GuzzleHttp\json_decode($widgetsVisibilityJson, true);

        $settingsWidgetsSettingsDto = new self();

        foreach($widgetVisibilityArrays as $widgetVisibilityArray){

            $widgetVisibilityJson        = \GuzzleHttp\json_encode($widgetVisibilityArray);
            $settingsWidgetVisibilityDto = SettingsWidgetVisibilityDto::fromJson($widgetVisibilityJson);
            $settingsWidgetsSettingsDto->addWidgetVisibility($settingsWidgetVisibilityDto);

        }

        return $settingsWidgetsSettingsDto;
    }

    /**
     * @return string
     */
    public function toJson(): string{

        $arrayOfVisibilitiesJsons = [];

        foreacH($this->getWidgetsVisibility() as $widgetVisibility ){
            $arrayOfVisibilitiesJsons[] = $widgetVisibility->toArray();
        }

        $array = [
            self::KEY_WIDGETS_VISIBILITY => $arrayOfVisibilitiesJsons,
        ];

        $json = json_encode($array);

        return $json;
    }

    public function toArray(): array {
        $json  = $this->toJson();
        $array = json_decode($json, true);

        return $array;
    }

}