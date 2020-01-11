<?php

namespace App\Controller\Page;

use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use App\Services\Settings\SettingsLoader;
use App\Services\Settings\SettingsSaver;
use App\Services\Translator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsFinancesController extends AbstractController {

    const TWIG_FINANCES_SETTINGS_TEMPLATE = 'page-elements/settings/components/finances-settings.html.twig' ;

    const KEY_ALL_ROWS_DATA = 'all_rows_data';

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

    public function __construct(Translator $translator, SettingsSaver $settings_saver, SettingsLoader $settings_loader, SettingsViewController $settings_view_controller) {
        $this->settings_view_controller = $settings_view_controller;
        $this->settings_loader          = $settings_loader;
        $this->settings_saver           = $settings_saver;
        $this->translator               = $translator;
    }

    /**
     * Handles updating settings of dashboard - widgets visibility
     * In this case it's not single row update but entire setting string
     * So the data passed in is not single row but all rows in table
     * It's important to understand that import is done for all currencies as for example new might be added
     * @Route("/api/settings-finances/update-currencies", name="settings_finances_update_currencies", methods="POST")
     * @param Request $request
     * @return Response
     * @throws ExceptionDuplicatedTranslationKey
     * @throws Exception
     */
    public function updateFinancesCurrenciesSetting(Request $request){

        if (!$request->request->has(self::KEY_ALL_ROWS_DATA)) {
            $message = $this->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ALL_ROWS_DATA;
            throw new Exception($message);
        }

        $all_rows_data           = $request->request->get(self::KEY_ALL_ROWS_DATA);
        $currencies_setting_dtos = [];

        foreach($all_rows_data as $row_data){

            if( !array_key_exists(SettingsCurrencyDTO::KEY_NAME, $row_data)){
                $message = $this->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_NAME;
                throw new \Exception($message);
            }

            if( !array_key_exists(SettingsCurrencyDTO::KEY_SYMBOL, $row_data)){
                $message = $this->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_SYMBOL;
                throw new \Exception($message);
            }

            if( !array_key_exists(SettingsCurrencyDTO::KEY_MULTIPLIER, $row_data)){
                $message = $this->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_MULTIPLIER;
                throw new \Exception($message);
            }

            if( !array_key_exists(SettingsCurrencyDTO::KEY_IS_DEFAULT, $row_data)){
                $message = $this->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_IS_DEFAULT;
                throw new \Exception($message);
            }


            $is_default = filter_var($row_data[SettingsCurrencyDTO::KEY_IS_DEFAULT], FILTER_VALIDATE_BOOLEAN);;
            $name       = trim($row_data[SettingsCurrencyDTO::KEY_NAME]);
            $symbol     = trim($row_data[SettingsCurrencyDTO::KEY_SYMBOL]);
            $multiplier = trim($row_data[SettingsCurrencyDTO::KEY_MULTIPLIER]);

            $currency_setting_dto = new SettingsCurrencyDTO();
            $currency_setting_dto->setName($name);
            $currency_setting_dto->setSymbol($symbol);
            $currency_setting_dto->setMultiplier($multiplier);
            $currency_setting_dto->setIsDefault($is_default);

            $currencies_setting_dtos[] = $currency_setting_dto;
        }

        $this->settings_saver->saveSettingsForFinancesCurrencies($currencies_setting_dtos);
        return $this->settings_view_controller->renderSettingsTemplate(false);
    }

    /**
     * @param array|null $currencies_setting_dtos
     * @return SettingsFinancesDTO
     * @throws Exception
     */
    public static function buildFinancesSettingsDto(array $currencies_setting_dtos = null){

        if( empty($currencies_setting_dtos) ){
            $currencies_setting_dtos   = [];
            $currencies_setting_dtos[] = new SettingsCurrencyDTO();
        }

        $finances_settings_dto = new SettingsFinancesDTO();
        $finances_settings_dto->setSettingsCurrencyDtos($currencies_setting_dtos);

        return $finances_settings_dto;
    }
}
