<?php

namespace App\Services\Settings;

use App\DTO\Settings\Finances\SettingsCurrencyDto;
use App\DTO\Settings\Finances\SettingsFinancesDto;
use App\DTO\Settings\SettingsDashboardDto;
use App\Entity\Setting;
use App\Repository\SettingRepository;
use Exception;

/**
 * This class is responsible for fetching settings json from DB
 * Class SettingsLoader
 * @package App\Services\Files
 */
class SettingsLoader {

    const SETTING_NAME_DASHBOARD = 'dashboard';
    const SETTING_NAME_NOTIFICATIONS = 'notifications';
    const SETTING_NAME_MODULES   = 'modules';
    const SETTING_NAME_FINANCES  = 'finances';

    /**
     * DatabaseExporter constructor.
     * @throws Exception
     */
    public function __construct(
        private readonly SettingRepository $settingRepository
    ) {
    }

    /**
     * @return Setting|null
     */
    public function getSettingsForDashboard(): ?Setting {
        $setting = $this->settingRepository->getSettingByName(self::SETTING_NAME_DASHBOARD);
        return $setting;
    }

    /**
     * @return Setting|null
     */
    public function getSettingsForNotifications(): ?Setting
    {
        return $this->settingRepository->getSettingByName(self::SETTING_NAME_NOTIFICATIONS);
    }

    /**
     * @return Setting|null
     */
    public function getModulesSettings(): ?Setting
    {
        return $this->settingRepository->getSettingByName(self::SETTING_NAME_MODULES);
    }

    /**
     * Check if given dashboard widget is enabled or not
     *
     * @param string $widgetName
     *
     * @return bool
     *
     * @throws Exception
     */
    public function isDashboardWidgetVisible(string $widgetName): bool
    {
        $settings = $this->getSettingsForDashboard();
        if (empty($settings)) {
            return true;
        }

        $dashboardSettings = SettingsDashboardDto::fromJson($settings->getValue());
        foreach ($dashboardSettings->getWidgetSettings()->getWidgetsVisibility() as $widgetInfo) {
            if ($widgetName === $widgetInfo->getName()) {
                return $widgetInfo->isVisible();
            }
        }

        return true;
    }

    /**
     * @return Setting|null
     */
    public function getSettingsForFinances(): ?Setting {
        $setting = $this->settingRepository->getSettingByName(self::SETTING_NAME_FINANCES);
        return $setting;
    }

    /**
     * @return SettingsCurrencyDto[]
     * @throws Exception
     */
    public function getCurrenciesDtosForSettingsFinances(): array {
        $setting                = $this->getSettingsForFinances();
        $currenciesSettingDtos  = [];

        if( !empty($setting) ) {
            $settingsFinancesJson   = $setting->getValue();
            $settingsFinancesDto    = SettingsFinancesDto::fromJson($settingsFinancesJson);
            $currenciesSettingDtos  = $settingsFinancesDto->getSettingsCurrencyDtos();
        }

        return $currenciesSettingDtos;
    }

}