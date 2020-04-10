<?php
namespace App\Controller\Core;

use App\Controller\Page\SettingsController;
use App\Controller\Page\SettingsDashboardController;
use App\Controller\Page\SettingsFinancesController;
use App\Controller\Page\SettingsValidationController;
use App\Services\Settings\SettingsLoader;
use App\Services\Settings\SettingsSaver;

class Settings {

    /**
     * @var SettingsSaver $settings_saver
     */
    public $settings_saver;

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

    /**
     * @var SettingsFinancesController $setting_finances_controller
     */
    public $setting_finances_controller;

    /**
     * @var SettingsValidationController $settings_validation_controller
     */
    private $settings_validation_controller;

    public function __construct(
        SettingsSaver                $settings_saver,
        SettingsLoader               $settings_loader,
        SettingsController           $settings_controller,
        SettingsDashboardController  $settings_dashboard_controller,
        SettingsFinancesController   $setting_finances_controller,
        SettingsValidationController $settings_validation_controller
    ) {
        $this->settings_saver                   = $settings_saver;
        $this->settings_loader                  = $settings_loader;
        $this->settings_controller              = $settings_controller;
        $this->settings_dashboard_controller    = $settings_dashboard_controller;
        $this->settings_validation_controller   = $settings_validation_controller;
        $this->setting_finances_controller      = $setting_finances_controller;
    }

}