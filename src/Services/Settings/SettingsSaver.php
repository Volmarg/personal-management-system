<?php

namespace App\Services\Settings;

use App\Controller\Page\SettingsController;
use App\Controller\Page\SettingsDashboardController;
use App\Controller\Utils\Application;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDTO;
use App\DTO\Settings\SettingsDashboardDTO;
use App\Entity\Setting;

/**
 * Class SettingsSaver
 * @package App\Services\Files
 */
class SettingsSaver {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var SettingsLoader $settings_loader
     */
    private $settings_loader;

    /**
     * @var SettingsController $settings_controller
     */
    private $settings_controller;

    /**
     * @param Application $app
     * @param SettingsLoader $settings_loader
     * @param SettingsController $settings_controller
     */
    public function __construct(
        Application        $app,
        SettingsLoader     $settings_loader,
        SettingsController $settings_controller

    ) {
        $this->app = $app;
        $this->settings_loader = $settings_loader;
        $this->settings_controller = $settings_controller;
    }

    public function saveSettingsForDashboardFromDto(SettingsDashboardDTO $dto){
        $json = $dto->toJson();

        $setting = new Setting();
        $setting->setName(SettingsLoader::SETTING_NAME_DASHBOARD);
        $setting->setValue($json);

        $this->app->em->persist($setting);
        $this->app->em->flush();
    }

    /**
     * @param SettingsWidgetVisibilityDTO[] $array_of_widgets_visibility_dto
     * @throws \Exception
     */
    public function saveSettingsForDashboardWidgetsVisibility(array $array_of_widgets_visibility_dto): void {
        $dto     = $this->settings_controller->buildSettingsDashboardDtoFromSettingsJsonInDb();
        $setting = $this->settings_loader->getSettingsForDashboard();

        $are_settings_in_db = !empty($dto);

        if( $are_settings_in_db ){
            $dto->getWidgetSettings()->setWidgetVisibility($array_of_widgets_visibility_dto);
        }else{
            $dto = SettingsDashboardController::buildDashboardSettingsDto($array_of_widgets_visibility_dto);
        }

        $dashboard_settings_json = $dto->toJson();

        $setting->setName(SettingsController::KEY_DASHBOARD_SETTINGS);
        $setting->setValue($dashboard_settings_json);

        $this->app->em->persist($setting);
        $this->app->em->flush();

    }

}