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
     * @var SettingsSaver $settingsSaver
     */
    public $settingsSaver;

    /**
     * @var SettingsLoader $settingsLoader
     */
    public $settingsLoader;

    /***
     * @var SettingsController $settingsController
     */
    public $settingsController;

    /**
     * @var SettingsDashboardController $settingsDashboardController
     */
    public $settingsDashboardController;

    /**
     * @var SettingsFinancesController $settingFinancesController
     */
    public $settingFinancesController;

    /**
     * @var SettingsValidationController $settingsValidationController
     */
    private $settingsValidationController;

    public function __construct(
        SettingsSaver                $settingsSaver,
        SettingsLoader               $settingsLoader,
        SettingsController           $settingsController,
        SettingsDashboardController  $settingsDashboardController,
        SettingsFinancesController   $settingFinancesController,
        SettingsValidationController $settingsValidationController
    ) {
        $this->settingsSaver                = $settingsSaver;
        $this->settingsLoader               = $settingsLoader;
        $this->settingsController           = $settingsController;
        $this->settingsDashboardController  = $settingsDashboardController;
        $this->settingsValidationController = $settingsValidationController;
        $this->settingFinancesController    = $settingFinancesController;
    }

}