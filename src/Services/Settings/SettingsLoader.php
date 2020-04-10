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
        $setting = $this->repositories->settingRepository->getSettingsForDashboard();
        return $setting;
    }

    /**
     * @return Setting|null
     */
    public function getSettingsForFinances(): ?Setting {
        $setting = $this->repositories->settingRepository->getSettingsForFinances();
        return $setting;
    }

    /**
     * @return SettingsCurrencyDTO[]
     * @throws Exception
     */
    public function getCurrenciesDtosForSettingsFinances(): array {
        $setting                  = $this->getSettingsForFinances();
        $currencies_setting_dtos  = [];

        if( !empty($setting) ) {
            $settings_finances_json   = $setting->getValue();
            $settings_finances_dto    = SettingsFinancesDTO::fromJson($settings_finances_json);
            $currencies_setting_dtos  = $settings_finances_dto->getSettingsCurrencyDtos();
        }

        return $currencies_setting_dtos;
    }

}