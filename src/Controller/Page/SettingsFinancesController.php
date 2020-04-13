<?php

namespace App\Controller\Page;

use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\DTO\Settings\SettingValidationDTO;
use App\Services\Settings\SettingsLoader;
use App\Services\Settings\SettingsSaver;
use App\Services\Core\Translator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsFinancesController extends AbstractController {

    /**
     * @var Translator $translator
     */
    private $translator;

    /**
     * @var SettingsSaver $settings_saver
     */
    private $settings_saver;

    /**
     * @var SettingsLoader $settings_loader
     */
    private $settings_loader;

    /**
     * @var SettingsViewController $settings_view_controller
     */
    private $settings_view_controller;

    /**
     * @var SettingsValidationController $settings_validation_controller
     */
    private $settings_validation_controller;

    public function __construct(
        Translator                   $translator,
        SettingsSaver                $settings_saver,
        SettingsLoader               $settings_loader,
        SettingsViewController       $settings_view_controller,
        SettingsValidationController $settings_validation_controller
    ) {
        $this->settings_validation_controller = $settings_validation_controller;
        $this->settings_view_controller       = $settings_view_controller;
        $this->settings_loader                = $settings_loader;
        $this->settings_saver                 = $settings_saver;
        $this->translator                     = $translator;
    }

    /**
     * @param array|null $currencies_setting_dtos
     * @return SettingsFinancesDTO
     * @throws Exception
     */
    public static function buildFinancesSettingsDtoFromCurrenciesSettingsDtos(array $currencies_setting_dtos = null){

        if( empty($currencies_setting_dtos) ){
            $currencies_setting_dtos   = [];
            $currencies_setting_dtos[] = new SettingsCurrencyDTO();
        }

        $finances_settings_dto = new SettingsFinancesDTO();
        $finances_settings_dto->setSettingsCurrencyDtos($currencies_setting_dtos);

        return $finances_settings_dto;
    }

    /**
     * This function enforce the update of all the currencies when default currency is changed
     * @param SettingsCurrencyDTO[]  $currencies_settings_dtos
     * @param SettingsCurrencyDTO    $new_default_setting_currency_dto
     * @param string                 $array_index_of_updated_setting
     * @return SettingsCurrencyDTO[]
     */
    public function handleDefaultCurrencyChange(array $currencies_settings_dtos, SettingsCurrencyDTO $new_default_setting_currency_dto, string $array_index_of_updated_setting){

        foreach( $currencies_settings_dtos as &$currency_setting_dto ){
            $currency_setting_dto->setIsDefault(false);
        }

        $currencies_settings_dtos[$array_index_of_updated_setting] = $new_default_setting_currency_dto;

        return $currencies_settings_dtos;
    }

    /**
     * This function enforce the update of all the currencies when default currency is changed
     * @param SettingsCurrencyDTO[]  $currencies_settings_dtos
     * @param SettingsCurrencyDTO    $new_setting_currency_dto
     * @param string                 $array_index_of_updated_setting
     * @return SettingsCurrencyDTO[]
     */
    public function handleCurrencyUpdate(array $currencies_settings_dtos, SettingsCurrencyDTO $new_setting_currency_dto, string $array_index_of_updated_setting){
        $currencies_settings_dtos[$array_index_of_updated_setting] = $new_setting_currency_dto;
        return $currencies_settings_dtos;
    }

    /**
     * @param SettingsCurrencyDTO $settings_currency_dto
     * @return SettingValidationDTO
     * @throws Exception
     */
    public function addCurrencyToFinancesCurrencySettings(SettingsCurrencyDTO $settings_currency_dto): SettingValidationDTO {

        $setting_validation_dto = $this->settings_validation_controller->isValueByKeyUnique($settings_currency_dto);

        if( !$setting_validation_dto->isValid() ){
            return $setting_validation_dto;
        }

        $settings_currencies_dtos_in_db = $this->settings_loader->getCurrenciesDtosForSettingsFinances();

        if( !empty($settings_currencies_dtos_in_db) ){
            $settings_currencies_dtos_in_db[] = $settings_currency_dto;
            $this->settings_saver->saveFinancesSettingsForCurrenciesSettings($settings_currencies_dtos_in_db);
            return $setting_validation_dto;
        }

        $this->settings_saver->saveFinancesSettingsForCurrenciesSettings([$settings_currency_dto]);
        return $setting_validation_dto;
    }

}
