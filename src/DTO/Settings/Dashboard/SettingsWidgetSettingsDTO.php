<?php

namespace App\DTO\Settings\Dashboard;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDTO;

class SettingsWidgetSettingsDTO extends AbstractDTO implements dtoInterface{

    const KEY_WIDGETS_VISIBILITY = 'widgets_visibility';

    /**
     * @var SettingsWidgetVisibilityDTO[]
     */
    private $widgetsVisibility = [];

    /**
     * @return SettingsWidgetVisibilityDTO[]
     */
    public function getWidgetsVisibility(): array {
        return $this->widgetsVisibility;
    }

    /**
     * @param SettingsWidgetVisibilityDTO $widgetVisibility
     */
    public function addWidgetVisibility(SettingsWidgetVisibilityDTO $widgetVisibility): void {
        array_push($this->widgetsVisibility, $widgetVisibility);
    }

    /**
     * @param array $widgetsVisibilityDtos
     * @throws \Exception
     */
    public function setWidgetVisibility(array $widgetsVisibilityDtos){

        $hasDto = reset($widgetsVisibilityDtos) instanceof SettingsWidgetVisibilityDTO;

        if( !$hasDto ){
            throw new \Exception("There are no SettingsWidgetVisibilityDTO in array ");
        }

        $this->widgetsVisibility = $widgetsVisibilityDtos;
    }

    /**
     * @param string $widgetsSettingsJson
     * @return SettingsWidgetSettingsDTO
     * @throws \Exception
     */
    public static function fromJson(string $widgetsSettingsJson): self{
        $widgetsSettingsArray   = \GuzzleHttp\json_decode($widgetsSettingsJson, true);
        $widgetsVisibilityJson  = self::checkAndGetKey($widgetsSettingsArray, self::KEY_WIDGETS_VISIBILITY);
        $widgetVisibilityArrays = \GuzzleHttp\json_decode($widgetsVisibilityJson, true);

        $settingsWidgetsSettingsDto = new self();

        foreach($widgetVisibilityArrays as $widgetVisibilityArray){

            $widgetVisibilityJson = \GuzzleHttp\json_encode($widgetVisibilityArray);
            $settingsWidgetVisibilityDto = SettingsWidgetVisibilityDTO::fromJson($widgetVisibilityJson);
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