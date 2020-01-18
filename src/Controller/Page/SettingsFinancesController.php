<?php

namespace App\Controller\Page;

use App\Controller\Utils\AjaxResponse;
use App\DTO\CallStatusDTO;
use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\DTO\Settings\SettingValidationDTO;
use App\Form\Page\Settings\Finances\CurrencyType;
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
    public function updateFinancesCurrenciesSetting(Request $request): Response {

        if( !$request->request->has(SettingsCurrencyDTO::KEY_NAME) ){
            $message = $this->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_NAME;
            throw new \Exception($message);
        }

        if( !$request->request->has(SettingsCurrencyDTO::KEY_NAME) ){
            $message = $this->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_NAME;
            throw new \Exception($message);
        }

        if( !$request->request->has(SettingsCurrencyDTO::KEY_SYMBOL) ){
            $message = $this->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_SYMBOL;
            throw new \Exception($message);
        }

        if( !$request->request->has(SettingsCurrencyDTO::KEY_MULTIPLIER) ){
            $message = $this->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_MULTIPLIER;
            throw new \Exception($message);
        }

        if( !$request->request->has(SettingsCurrencyDTO::KEY_IS_DEFAULT) ){
            $message = $this->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_IS_DEFAULT;
            throw new \Exception($message);
        }

        if( !$request->request->has(SettingsController::KEY_BEFORE_UPDATE_STATE) ){
            $message = $this->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsController::KEY_BEFORE_UPDATE_STATE;
            throw new \Exception($message);
        }

        $code    = 200;
        $message = $this->translator->translate('settings.finances.type.messages.success');

        $is_default          = filter_var($request->request->get(SettingsCurrencyDTO::KEY_IS_DEFAULT), FILTER_VALIDATE_BOOLEAN);;
        $name                = trim($request->request->get(SettingsCurrencyDTO::KEY_NAME));
        $symbol              = trim($request->request->get(SettingsCurrencyDTO::KEY_SYMBOL));
        $multiplier          = (float) trim($request->request->get(SettingsCurrencyDTO::KEY_MULTIPLIER));
        $before_update_state = trim($request->request->get(SettingsController::KEY_BEFORE_UPDATE_STATE));

        $before_update_currency_setting_dto = SettingsCurrencyDTO::fromJson($before_update_state);

        $new_currency_setting_dto = new SettingsCurrencyDTO();
        $new_currency_setting_dto->setName($name);
        $new_currency_setting_dto->setSymbol($symbol);
        $new_currency_setting_dto->setMultiplier($multiplier);
        $new_currency_setting_dto->setIsDefault($is_default);

        $currencies_settings_dtos       = $this->settings_loader->getCurrenciesDtosForSettingsFinances();
        $array_index_of_updated_setting = null;

        foreach( $currencies_settings_dtos as $index => $currency_setting_dto_from_db ){
            if( $currency_setting_dto_from_db->getName() === $before_update_currency_setting_dto->getName() ){
                $array_index_of_updated_setting = $index;
                break;
            }
        }

        if( is_null($array_index_of_updated_setting) ){
            $code    = 500;
            $message = $this->translator->translate("settings.finances.type.messages.couldNotFindSettingWithThisName");
        }

        if(
                $before_update_currency_setting_dto->isDefault()
            &&  !$new_currency_setting_dto->isDefault()
        ){
            $code    = 400;
            $message = $this->translator->translate("settings.finances.type.messages.canNotUnsetTheTheDefaultPropertyForDefaultCurrency");
        }

        if( 200 === $code ){
            $currencies_settings_dtos = $this->handleDefaultCurrencyChange($currencies_settings_dtos, $new_currency_setting_dto, $array_index_of_updated_setting);
            $this->settings_saver->saveSettingsForFinancesCurrencies($currencies_settings_dtos);
        }

        return AjaxResponse::buildResponseForAjaxCall($code, $message);
    }

    /**
     * This function handles removal of the currency from finances setting
     * @Route("/api/settings-finances/remove-currency/{name}", name="settings_finances_remove_currency", methods="POST")
     * @param Request $request
     * @param string $name
     * @return string
     * @throws Exception
     */
    public function removeFinancesCurrencySetting(Request $request, string $name){

        $currencies_settings_dtos = $this->settings_loader->getCurrenciesDtosForSettingsFinances();
        $currency_existed        = false;

        foreach( $currencies_settings_dtos as $index => $currency_setting_dto ){

            if( $currency_setting_dto->getName() === $name ){

                if( $currency_setting_dto->isDefault() ){
                    $message = $this->translator->translate("settings.finances.type.messages.defaultCurrencyCanNotBeRemove");
                    return new Response($message, 500);
                }

                unset($currencies_settings_dtos[$index]);
                $currency_existed = true;
                break;
            }

        }

        if( !$currency_existed ){
            $message = $this->translator->translate("settings.finances.type.messages.couldNotFindCurrencyForGivenName");
            return new Response($message, 500);
        }

        $this->settings_saver->saveSettingsForFinancesCurrencies($currencies_settings_dtos);

        $rendered_view = $this->settings_view_controller->renderSettingsTemplate(true);
        return $rendered_view->getContent(); //todo: handle template
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

    /**
     * @param Request $request
     * @return CallStatusDTO
     * @throws Exception
     */
    public function handleFinancesCurrencyForm(Request $request): CallStatusDTO {
        $currency_type_form = $this->createForm(CurrencyType::class);
        $currency_type_form->handleRequest($request);

        $call_status_dto = new CallStatusDTO();

        if( $currency_type_form->isSubmitted() && $currency_type_form->isValid() ){
            $form_data  = $currency_type_form->getData();
            $name       = $form_data[SettingsCurrencyDTO::KEY_NAME]       ?? "";
            $symbol     = $form_data[SettingsCurrencyDTO::KEY_SYMBOL]     ?? "";
            $multiplier = $form_data[SettingsCurrencyDTO::KEY_MULTIPLIER] ?? "";
            $is_default = $form_data[SettingsCurrencyDTO::KEY_IS_DEFAULT] ?? "";

            // handle checking if default value already exist

            if( $this->isDefaultCurrencySettingSet() && $is_default ){
                $message = $this->translator->translate("forms.CurrencyType.messages.failure.defaultCurrencyIsAlreadySet");
                $call_status_dto->setMessage($message);
                $call_status_dto->setCode(400);
            } else {
                $settings_finances_currency_dto = new SettingsCurrencyDTO();
                $settings_finances_currency_dto->setName($name);
                $settings_finances_currency_dto->setSymbol($symbol);
                $settings_finances_currency_dto->setMultiplier($multiplier);
                $settings_finances_currency_dto->setIsDefault($is_default);

                $setting_validation_dto = $this->addCurrencyToFinancesCurrencySettings($settings_finances_currency_dto);

                $call_status_dto->setIsSuccess($setting_validation_dto->isValid());

                if( !$setting_validation_dto->isValid() ){
                    $call_status_dto->setMessage($setting_validation_dto->getMessage());
                    $call_status_dto->setCode(400);
                }else {
                    $call_status_dto->setCode(200);
                }

            }

        }elseif( $currency_type_form->isSubmitted() && !$currency_type_form->isValid() ){
            $call_status_dto->setFailureReason(CallStatusDTO::KEY_FAILURE_REASON_FORM_VALIDATION);
        }

        return $call_status_dto;
    }

    /**
     * This function enforce the update of all the currencies when default currency is changed
     * @param SettingsCurrencyDTO[]  $currencies_settings_dtos
     * @param SettingsCurrencyDTO    $new_default_setting_currency_dto
     * @param string                 $array_index_of_updated_setting
     * @return SettingsCurrencyDTO[]
     */
    private function handleDefaultCurrencyChange(array $currencies_settings_dtos, SettingsCurrencyDTO $new_default_setting_currency_dto, string $array_index_of_updated_setting){

        foreach( $currencies_settings_dtos as &$currency_setting_dto ){
            $currency_setting_dto->setIsDefault(false);
        }

        $currencies_settings_dtos[$array_index_of_updated_setting] = $new_default_setting_currency_dto;

        return $currencies_settings_dtos;
    }

    /**
     * @param SettingsCurrencyDTO $settings_finances_currency_dto
     * @return SettingValidationDTO
     * @throws Exception
     */
    private function addCurrencyToFinancesCurrencySettings(SettingsCurrencyDTO $settings_finances_currency_dto): SettingValidationDTO {

        $setting_validation_dto = $this->settings_validation_controller->isValueByKeyUnique($settings_finances_currency_dto);

        if( !$setting_validation_dto->isValid() ){
            return $setting_validation_dto;
        }

        $finances_currency_settings     = $this->settings_loader->getSettingsForFinances();

        if( !empty($finances_currency_settings) ){
            $finances_currency_settings_json = $finances_currency_settings->getValue();

            $finances_settings_dto                     = SettingsFinancesDTO::fromJson($finances_currency_settings_json);
            $finances_settings_dto->addSettingsCurrencyDto($settings_finances_currency_dto);

            $saved_settings_finances_currencies_dtos   = $finances_settings_dto->getSettingsCurrencyDtos();

            $this->settings_saver->saveSettingsForFinancesCurrencies($saved_settings_finances_currencies_dtos);
            return $setting_validation_dto;
        }

        $this->settings_saver->saveSettingsForFinancesCurrencies([$settings_finances_currency_dto]);
        return $setting_validation_dto;
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function isDefaultCurrencySettingSet(): bool {
        $currencies_setting_dtos = $this->settings_loader->getCurrenciesDtosForSettingsFinances();

        foreach( $currencies_setting_dtos as $currency_setting_dto ){
            if ( $currency_setting_dto->isDefault() ){
                return true;
            }
        }

        return false;
    }
}
