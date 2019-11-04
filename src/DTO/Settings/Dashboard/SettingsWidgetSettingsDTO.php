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
    private $widgets_visibility = [];

    /**
     * @return SettingsWidgetVisibilityDTO[]
     */
    public function getWidgetsVisibility(): array {
        return $this->widgets_visibility;
    }

    /**
     * @param SettingsWidgetVisibilityDTO $widget_visibility
     */
    public function addWidgetVisibility(SettingsWidgetVisibilityDTO $widget_visibility): void {
        array_push($this->widgets_visibility, $widget_visibility);
    }

    /**
     * @param array $widgets_visibility_dtos
     * @throws \Exception
     */
    public function setWidgetVisibility(array $widgets_visibility_dtos){

        $has_dto = reset($widgets_visibility_dtos) instanceof SettingsWidgetVisibilityDTO;

        if( !$has_dto ){
            throw new \Exception("There are no SettingsWidgetVisibilityDTO in array ");
        }

        $this->widgets_visibility = $widgets_visibility_dtos;
    }

    /**
     * @param string $widgets_settings_json
     * @return SettingsWidgetSettingsDTO
     * @throws \Exception
     */
    public static function fromJson(string $widgets_settings_json): self{
        $widgets_settings_array     = \GuzzleHttp\json_decode($widgets_settings_json, true);
        $widgets_visibility_json    = self::checkAndGetKey($widgets_settings_array, self::KEY_WIDGETS_VISIBILITY);
        $widget_visibility_arrays   = \GuzzleHttp\json_decode($widgets_visibility_json, true);

        $settings_widgets_settings_dto = new self();

        foreach($widget_visibility_arrays as $widget_visibility_array){

            $widget_visibility_json = \GuzzleHttp\json_encode($widget_visibility_array);
            $settings_widget_visibility_dto = SettingsWidgetVisibilityDTO::fromJson($widget_visibility_json);
            $settings_widgets_settings_dto->addWidgetVisibility($settings_widget_visibility_dto);

        }

        return $settings_widgets_settings_dto;
    }

    /**
     * @return string
     */
    public function toJson(): string{

        $array_of_visibilities_jsons = [];

        foreacH($this->getWidgetsVisibility() as $widget_visibility ){
            $array_of_visibilities_jsons[] = $widget_visibility->toArray();
        }

        $array = [
            self::KEY_WIDGETS_VISIBILITY => $array_of_visibilities_jsons,
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