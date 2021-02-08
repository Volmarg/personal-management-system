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
     * @var SettingsSaver $settingsSaver
     */
    private $settingsSaver;

    /**
     * @var SettingsLoader $settingsLoader
     */
    private $settingsLoader;

    /**
     * @var SettingsViewController $settingsViewController
     */
    private $settingsViewController;

    /**
     * @var SettingsValidationController $settingsValidationController
     */
    private $settingsValidationController;

    public function __construct(
        Translator                   $translator,
        SettingsSaver                $settingsSaver,
        SettingsLoader               $settingsLoader,
        SettingsViewController       $settingsViewController,
        SettingsValidationController $settingsValidationController
    ) {
        $this->settingsValidationController = $settingsValidationController;
        $this->settingsViewController       = $settingsViewController;
        $this->settingsLoader               = $settingsLoader;
        $this->settingsSaver                = $settingsSaver;
        $this->translator                   = $translator;
    }

    /**
     * @param array|null $currenciesSettingDtos
     * @return SettingsFinancesDTO
     * @throws Exception
     */
    public static function buildFinancesSettingsDtoFromCurrenciesSettingsDtos(array $currenciesSettingDtos = null){

        if( empty($currenciesSettingDtos) ){
            $currenciesSettingDtos   = [];
            $currenciesSettingDtos[] = new SettingsCurrencyDTO();
        }

        $financesSettingsDto = new SettingsFinancesDTO();
        $financesSettingsDto->setSettingsCurrencyDtos($currenciesSettingDtos);

        return $financesSettingsDto;
    }

    /**
     * This function enforce the update of all the currencies when default currency is changed
     * @param SettingsCurrencyDTO[]  $currenciesSettingsDtos
     * @param SettingsCurrencyDTO    $newDefaultSettingCurrencyDto
     * @param string                 $arrayIndexOfUpdatedSetting
     * @return SettingsCurrencyDTO[]
     */
    public function handleDefaultCurrencyChange(array $currenciesSettingsDtos, SettingsCurrencyDTO $newDefaultSettingCurrencyDto, string $arrayIndexOfUpdatedSetting){

        foreach($currenciesSettingsDtos as &$currency_setting_dto ){
            $currency_setting_dto->setIsDefault(false);
        }

        $currenciesSettingsDtos[$arrayIndexOfUpdatedSetting] = $newDefaultSettingCurrencyDto;

        return $currenciesSettingsDtos;
    }

    /**
     * This function enforce the update of all the currencies when default currency is changed
     * @param SettingsCurrencyDTO[]  $currenciesSettingsDtos
     * @param SettingsCurrencyDTO    $newSettingCurrencyDto
     * @param string                 $arrayIndexOfUpdatedSetting
     * @return SettingsCurrencyDTO[]
     */
    public function handleCurrencyUpdate(array $currenciesSettingsDtos, SettingsCurrencyDTO $newSettingCurrencyDto, string $arrayIndexOfUpdatedSetting){
        $currenciesSettingsDtos[$arrayIndexOfUpdatedSetting] = $newSettingCurrencyDto;
        return $currenciesSettingsDtos;
    }

    /**
     * @param SettingsCurrencyDTO $settingsCurrencyDto
     * @return SettingValidationDTO
     * @throws Exception
     */
    public function addCurrencyToFinancesCurrencySettings(SettingsCurrencyDTO $settingsCurrencyDto): SettingValidationDTO {

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
