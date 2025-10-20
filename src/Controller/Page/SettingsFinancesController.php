<?php

namespace App\Controller\Page;

use App\DTO\Settings\Finances\SettingsCurrencyDto;
use App\DTO\Settings\Finances\SettingsFinancesDto;
use App\DTO\Settings\SettingValidationDTO;
use App\Services\Settings\SettingsLoader;
use App\Services\Settings\SettingsSaver;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsFinancesController extends AbstractController {

    /**
     * @var SettingsSaver $settingsSaver
     */
    private $settingsSaver;

    /**
     * @var SettingsLoader $settingsLoader
     */
    private $settingsLoader;

    /**
     * @var SettingsValidationController $settingsValidationController
     */
    private $settingsValidationController;

    public function __construct(
        SettingsSaver                $settingsSaver,
        SettingsLoader               $settingsLoader,
        SettingsValidationController $settingsValidationController
    ) {
        $this->settingsValidationController = $settingsValidationController;
        $this->settingsLoader               = $settingsLoader;
        $this->settingsSaver                = $settingsSaver;
    }

    /**
     * @param array|null $currenciesSettingDtos
     *
     * @return SettingsFinancesDto
     * @throws Exception
     */
    public static function buildFinancesSettingsDtoFromCurrenciesSettingsDtos(array $currenciesSettingDtos = null){

        if( empty($currenciesSettingDtos) ){
            $currenciesSettingDtos   = [];
            $currenciesSettingDtos[] = new SettingsCurrencyDto();
        }

        $financesSettingsDto = new SettingsFinancesDto();
        $financesSettingsDto->setSettingsCurrencyDtos($currenciesSettingDtos);

        return $financesSettingsDto;
    }

    /**
     * This function enforce the update of all the currencies when default currency is changed
     *
     * @param SettingsCurrencyDto[] $currenciesSettingsDtos
     * @param SettingsCurrencyDto   $newDefaultSettingCurrencyDto
     * @param string                $arrayIndexOfUpdatedSetting
     *
     * @return SettingsCurrencyDto[]
     */
    public function handleDefaultCurrencyChange(array $currenciesSettingsDtos, SettingsCurrencyDto $newDefaultSettingCurrencyDto, string $arrayIndexOfUpdatedSetting){

        foreach($currenciesSettingsDtos as &$currency_setting_dto ){
            $currency_setting_dto->setIsDefault(false);
        }

        $currenciesSettingsDtos[$arrayIndexOfUpdatedSetting] = $newDefaultSettingCurrencyDto;

        return $currenciesSettingsDtos;
    }

    /**
     * This function enforce the update of all the currencies when default currency is changed
     *
     * @param SettingsCurrencyDto[]  $currenciesSettingsDtos
     * @param SettingsCurrencyDto    $newSettingCurrencyDto
     * @param string                 $arrayIndexOfUpdatedSetting
     *
     * @return SettingsCurrencyDto[]
     */
    public function handleCurrencyUpdate(array $currenciesSettingsDtos, SettingsCurrencyDto $newSettingCurrencyDto, string $arrayIndexOfUpdatedSetting){
        $currenciesSettingsDtos[$arrayIndexOfUpdatedSetting] = $newSettingCurrencyDto;
        return $currenciesSettingsDtos;
    }

    /**
     * @param SettingsCurrencyDto $settingsCurrencyDto
     * @return SettingValidationDTO
     * @throws Exception
     */
    public function addCurrencyToFinancesCurrencySettings(SettingsCurrencyDto $settingsCurrencyDto): SettingValidationDTO {

        $settingValidationDto = $this->settingsValidationController->isValueByKeyUnique($settingsCurrencyDto);

        if( !$settingValidationDto->isValid() ){
            return $settingValidationDto;
        }

        $settingsCurrenciesDtosInDb = $this->settingsLoader->getCurrenciesDtosForSettingsFinances();

        if( !empty($settingsCurrenciesDtosInDb) ){
            $settingsCurrenciesDtosInDb[] = $settingsCurrencyDto;
            $this->settingsSaver->saveFinancesSettingsForCurrenciesSettings($settingsCurrenciesDtosInDb);
            return $settingValidationDto;
        }

        $this->settingsSaver->saveFinancesSettingsForCurrenciesSettings([$settingsCurrencyDto]);
        return $settingValidationDto;
    }

}
