<?php
namespace App\Controller\Utils;

use App\Controller\Page\SettingsController;
use App\Controller\Page\SettingsDashboardController;
use App\Services\Settings\SettingsLoader;
use App\Services\Settings\SettingsSaver;

class Settings {

    /**
     * @var SettingsSaver $settings_saver
     */
    private $settings_saver;

    /**
     * @var SettingsLoader $settings_loader
     */
    public $settings_loader;

    /***
     * @var SettingsController $settings_controller
     */
    public $settings_controller;

    /**
     * @var SettingsDashboardController $settings_dashboard_controller
     */
    public $settings_dashboard_controller;

    public function __construct(
        SettingsSaver               $settings_saver,
        SettingsLoader              $settings_loader,
        SettingsController          $settings_controller,
        SettingsDashboardController $settings_dashboard_controller
    ) {
        $this->settings_saver                   = $settings_saver;
        $this->settings_loader                  = $settings_loader;
        $this->settings_controller              = $settings_controller;
        $this->settings_dashboard_controller    = $settings_dashboard_controller;
    }

}