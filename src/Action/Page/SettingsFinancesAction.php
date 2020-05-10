<?php

namespace App\Action\Page;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Page\SettingsController;
use App\DTO\CallStatusDTO;
use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\Form\Page\Settings\Finances\CurrencyType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsFinancesAction extends AbstractController {

    const TWIG_FINANCES_SETTINGS_TEMPLATE = 'page-elements/settings/components/finances-settings.html.twig' ;

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    /**
     * @var SettingsViewAction $settings_view_action
     */
    private $settings_view_action;

    public function __construct(Controllers $controllers, Application $app, SettingsViewAction $settings_view_action) {
        $this->app = $app;
        $this->controllers = $controllers;
        $this->settings_view_action = $settings_view_action;
    }

    /**
     * Handles updating settings of dashboard - widgets visibility
     * Contains special logic for handling the "default" currency change
     * @Route("/api/settings-finances/update-currencies", name="settings_finances_update_currencies", methods="POST")
     * @param Request $request
     * @return Response
     * 
     * @throws Exception
     */
    public function updateFinancesCurrenciesSetting(Request $request): Response {

        if( !$request->request->has(SettingsCurrencyDTO::KEY_NAME) ){
            $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_NAME;
            throw new Exception($message);
        }

        if( !$request->request->has(SettingsCurrencyDTO::KEY_NAME) ){
            $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_NAME;
            throw new Exception($message);
        }

        if( !$request->request->has(SettingsCurrencyDTO::KEY_SYMBOL) ){
            $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_SYMBOL;
            throw new Exception($message);
        }

        if( !$request->request->has(SettingsCurrencyDTO::KEY_MULTIPLIER) ){
            $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_MULTIPLIER;
            throw new Exception($message);
        }

        if( !$request->request->has(SettingsCurrencyDTO::KEY_IS_DEFAULT) ){
            $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsCurrencyDTO::KEY_IS_DEFAULT;
            throw new Exception($message);
        }

        if( !$request->request->has(SettingsController::KEY_BEFORE_UPDATE_STATE) ){
            $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsController::KEY_BEFORE_UPDATE_STATE;
            throw new Exception($message);
        }

        $code    = 200;
        $message = $this->app->translator->translate('settings.finances.type.messages.success');

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

        $currencies_settings_dtos       = $this->app->settings->settings_loader->getCurrenciesDtosForSettingsFinances();
        $array_index_of_updated_setting = null;

        foreach( $currencies_settings_dtos as $index => $currency_setting_dto_from_db ){
            if( $currency_setting_dto_from_db->getName() === $before_update_currency_setting_dto->getName() ){
                $array_index_of_updated_setting = $index;
            }

            if(
                    $currency_setting_dto_from_db->getName()        === $new_currency_setting_dto->getName()
                &&  $before_update_currency_setting_dto->getName()  !== $new_currency_setting_dto->getName()
            ){

                $code    = 500;
                $message = $this->app->translator->translate("settings.finances.type.messages.currencyWithThisNameAlreadyExist");
                break;
            }
        }

        if( is_null($array_index_of_updated_setting) ){
            $code    = 500;
            $message = $this->app->translator->translate("settings.finances.type.messages.couldNotFindSettingWithThisName");
        }

        if(
                $before_update_currency_setting_dto->isDefault()
            &&  !$new_currency_setting_dto->isDefault()
        ){
            $code    = 400;
            $message = $this->app->translator->translate("settings.finances.type.messages.canNotUnsetTheTheDefaultPropertyForDefaultCurrency");
        }

        if( 200 === $code ){

            if( $new_currency_setting_dto->isDefault() ){
                $currencies_settings_dtos = $this->controllers->getSettingsFinancesController()->handleDefaultCurrencyChange($currencies_settings_dtos, $new_currency_setting_dto, $array_index_of_updated_setting);
            }

            $currencies_settings_dtos = $this->controllers->getSettingsFinancesController()->handleCurrencyUpdate($currencies_settings_dtos, $new_currency_setting_dto, $array_index_of_updated_setting);
            $this->app->settings->settings_saver->saveFinancesSettingsForCurrenciesSettings($currencies_settings_dtos);
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

        $currencies_settings_dtos = $this->app->settings->settings_loader->getCurrenciesDtosForSettingsFinances();
        $currency_existed        = false;
        $name                    = trim($name);

        foreach( $currencies_settings_dtos as $index => $currency_setting_dto ){

            if( $currency_setting_dto->getName() === $name ){

                if( $currency_setting_dto->isDefault() ){
                    $message = $this->app->translator->translate("settings.finances.type.messages.defaultCurrencyCanNotBeRemove");
                    return new JsonResponse(["message" => $message], 500); //todo: refactor later with crud logic
                }

                unset($currencies_settings_dtos[$index]);
                $currency_existed = true;
                break;
            }

        }

        if( !$currency_existed ){
            $message = $this->app->translator->translate("settings.finances.type.messages.couldNotFindCurrencyForGivenName");
            return new JsonResponse(["message" => $message], 500); //todo: refactor later with crud logic
        }

        $this->app->settings->settings_saver->saveFinancesSettingsForCurrenciesSettings($currencies_settings_dtos);

        $rendered_view = $this->settings_view_action->renderSettingsTemplate(true);
        return $rendered_view;
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

        if( $currency_type_form->isSubmitted() && !$currency_type_form->isValid() ){
            $call_status_dto->setFailureReason(CallStatusDTO::KEY_FAILURE_REASON_FORM_VALIDATION);
            return $call_status_dto;
        }

        if( !$currency_type_form->isSubmitted() ) {
            $call_status_dto->setCode(200);
            return $call_status_dto;
        }

        $form_data  = $currency_type_form->getData();
        $name       = $form_data[SettingsCurrencyDTO::KEY_NAME]       ?? "";
        $symbol     = $form_data[SettingsCurrencyDTO::KEY_SYMBOL]     ?? "";
        $multiplier = $form_data[SettingsCurrencyDTO::KEY_MULTIPLIER] ?? "";
        $is_default = $form_data[SettingsCurrencyDTO::KEY_IS_DEFAULT] ?? "";

        $settings_currency_dto = new SettingsCurrencyDTO();
        $settings_currency_dto->setName($name);
        $settings_currency_dto->setSymbol($symbol);
        $settings_currency_dto->setMultiplier($multiplier);
        $settings_currency_dto->setIsDefault($is_default);

        $currencies_settings_dtos_in_db = $this->app->settings->settings_loader->getCurrenciesDtosForSettingsFinances();

        // handle adding currency with the same name
        foreach( $currencies_settings_dtos_in_db as $index => $currency_setting_dto_from_db ){
            if( $currency_setting_dto_from_db->getName() === $settings_currency_dto->getName() ){
                $message = $this->app->translator->translate("settings.finances.type.messages.currencyWithThisNameAlreadyExist");
                $call_status_dto->setCode(400);
                $call_status_dto->setMessage($message);
                return $call_status_dto;
            }
        }

        // handle checking if default value already exist and if so then unset all and set new
        if( $is_default ){
            $array_index_of_updated_setting = null;

            try{

                foreach( $currencies_settings_dtos_in_db as $index => &$currency_setting_dto_from_db ){
                    $currency_setting_dto_from_db->setIsDefault(false);
                }

                $currencies_settings_dtos_in_db[] = $settings_currency_dto;
                $this->app->settings->settings_saver->saveFinancesSettingsForCurrenciesSettings($currencies_settings_dtos_in_db);

                $call_status_dto->setCode(200);
                $call_status_dto->setIsSuccess(true);
            }catch(Exception $e){
                $message = $this->app->translator->translate("settings.finances.type.messages.couldNotHandleAddingNewCurrencyBeingDefaultValue");
                $call_status_dto->setCode(500);
                $call_status_dto->setMessage($message);
            }
        }

        // just add new record
        if( !$is_default ){

            $setting_validation_dto = $this->controllers->getSettingsFinancesController()->addCurrencyToFinancesCurrencySettings($settings_currency_dto);

            $call_status_dto->setIsSuccess($setting_validation_dto->isValid());

            if( !$setting_validation_dto->isValid() ){
                $call_status_dto->setMessage($setting_validation_dto->getMessage());
                $call_status_dto->setCode(400);
            }else {
                $call_status_dto->setCode(200);
            }
        }

        return $call_status_dto;
    }

}