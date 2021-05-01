<?php

namespace App\Services\Settings;

use App\Controller\Core\Repositories;
use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\Entity\Setting;
use Exception;

/**
 * This class is responsible for fetching settings json from DB
 * Class SettingsLoader
 * @package App\Services\Files
 */
class SettingsLoader {

    const SETTING_NAME_DASHBOARD = 'dashboard';
    const SETTING_NAME_MODULES   = 'modules';
    const SETTING_NAME_FINANCES  = 'finances';

    /**
     * @var Repositories $repositories
     */
    private $repositories;

    /**
     * DatabaseExporter constructor.
     * @param Repositories $repositories
     * @throws Exception
     */
    public function __construct(Repositories $repositories) {
        $this->repositories = $repositories;
    }

    /**
     * @return Setting|null
     */
    public function getSettingsForDashboard(): ?Setting {
        $setting = $this->repositories->settingRepository->getSettingByName(self::SETTING_NAME_DASHBOARD);
        return $setting;
    }

    /**
     * @return Setting|null
     */
    public function getSettingsForModules(): ?Setting {
        $setting = $this->repositories->settingRepository->getSettingByName(self::SETTING_NAME_MODULES);
        return $setting;
    }

    /**
     * @return Setting|null
     */
    public function getSettingsForFinances(): ?Setting {
        $setting = $this->repositories->settingRepository->getSettingByName(self::SETTING_NAME_FINANCES);
        return $setting;
    }

    /**
     * @return SettingsCurrencyDTO[]
     * @throws Exception
     */
    public function getCurrenciesDtosForSettingsFinances(): array {
        $setting                = $this->getSettingsForFinances();
        $currenciesSettingDtos  = [];

        if( !empty($setting) ) {
            $settingsFinancesJson   = $setting->getValue();
            $settingsFinancesDto    = SettingsFinancesDTO::fromJson($settingsFinancesJson);
            $currenciesSettingDtos  = $settingsFinancesDto->getSettingsCurrencyDtos();
        }

        return $currenciesSettingDtos;
    }

}