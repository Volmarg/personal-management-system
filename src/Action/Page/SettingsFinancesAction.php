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
use TypeError;

class SettingsFinancesAction extends AbstractController {

    const TWIG_FINANCES_SETTINGS_TEMPLATE = 'page-elements/settings/components/finances-settings.html.twig' ;

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    /**
     * @var SettingsViewAction $settingsViewAction
     */
    private SettingsViewAction $settingsViewAction;

    public function __construct(Controllers $controllers, Application $app, SettingsViewAction $settingsViewAction) {
        $this->app                = $app;
        $this->controllers        = $controllers;
        $this->settingsViewAction = $settingsViewAction;
    }

    /**
     * Handles updating settings of dashboard - widgets visibility
     * Contains special logic for handling the "default" currency change
     *
     * @Route("/api/settings-finances/update-currencies", name="settings_finances_update_currencies", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function updateFinancesCurrenciesSetting(Request $request): Response
    {

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

        $isDefault         = filter_var($request->request->get(SettingsCurrencyDTO::KEY_IS_DEFAULT), FILTER_VALIDATE_BOOLEAN);;
        $name              = trim($request->request->get(SettingsCurrencyDTO::KEY_NAME));
        $symbol            = trim($request->request->get(SettingsCurrencyDTO::KEY_SYMBOL));
        $multiplier        = (float) trim($request->request->get(SettingsCurrencyDTO::KEY_MULTIPLIER));
        $beforeUpdateState = trim($request->request->get(SettingsController::KEY_BEFORE_UPDATE_STATE));

        $beforeUpdateCurrencySettingDto = SettingsCurrencyDTO::fromJson($beforeUpdateState);

        $newCurrencySettingDto = new SettingsCurrencyDTO();
        $newCurrencySettingDto->setName($name);
        $newCurrencySettingDto->setSymbol($symbol);
        $newCurrencySettingDto->setMultiplier($multiplier);
        $newCurrencySettingDto->setIsDefault($isDefault);

        $currenciesSettingsDtos     = $this->app->settings->settingsLoader->getCurrenciesDtosForSettingsFinances();
        $arrayIndexOfUpdatedSetting = null;

        foreach( $currenciesSettingsDtos as $index => $currencySettingDtoFromDb ){
            if( $currencySettingDtoFromDb->getName() === $beforeUpdateCurrencySettingDto->getName() ){
                $arrayIndexOfUpdatedSetting = $index;
            }

            if(
                    $currencySettingDtoFromDb->getName()        === $newCurrencySettingDto->getName()
                &&  $beforeUpdateCurrencySettingDto->getName()  !== $newCurrencySettingDto->getName()
            ){

                $code    = 500;
                $message = $this->app->translator->translate("settings.finances.type.messages.currencyWithThisNameAlreadyExist");
                break;
            }
        }

        if( is_null($arrayIndexOfUpdatedSetting) ){
            $code    = 500;
            $message = $this->app->translator->translate("settings.finances.type.messages.couldNotFindSettingWithThisName");
        }

        if(
                $beforeUpdateCurrencySettingDto->isDefault()
            &&  !$newCurrencySettingDto->isDefault()
        ){
            $code    = 400;
            $message = $this->app->translator->translate("settings.finances.type.messages.canNotUnsetTheTheDefaultPropertyForDefaultCurrency");
        }

        if( 200 === $code ){

            if( $newCurrencySettingDto->isDefault() ){
                $currenciesSettingsDtos = $this->controllers->getSettingsFinancesController()->handleDefaultCurrencyChange($currenciesSettingsDtos, $newCurrencySettingDto, $arrayIndexOfUpdatedSetting);
            }

            $currenciesSettingsDtos = $this->controllers->getSettingsFinancesController()->handleCurrencyUpdate($currenciesSettingsDtos, $newCurrencySettingDto, $arrayIndexOfUpdatedSetting);
            $this->app->settings->settingsSaver->saveFinancesSettingsForCurrenciesSettings($currenciesSettingsDtos);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
    }

    /**
     * This function handles removal of the currency from finances setting
     * @Route("/api/settings-finances/remove-currency/{name}", name="settings_finances_remove_currency", methods="POST")
     * @param Request $request
     * @param string $name
     * @return JsonResponse
     * @throws Exception
     */
    public function removeFinancesCurrencySetting(Request $request, string $name): JsonResponse{

        try{
            $currenciesSettingsDtos = $this->app->settings->settingsLoader->getCurrenciesDtosForSettingsFinances();
            $currencyExisted        = false;
            $name                   = trim($name);

            foreach( $currenciesSettingsDtos as $index => $currencySettingDto ){

                if( $currencySettingDto->getName() === $name ){

                    if( $currencySettingDto->isDefault() ){
                        $message = $this->app->translator->translate("settings.finances.type.messages.defaultCurrencyCanNotBeRemove");
                        return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
                    }

                    unset($currenciesSettingsDtos[$index]);
                    $currencyExisted = true;
                    break;
                }

            }

            if( !$currencyExisted ){
                $message = $this->app->translator->translate("settings.finances.type.messages.couldNotFindCurrencyForGivenName");
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
            }

            $this->app->settings->settingsSaver->saveFinancesSettingsForCurrenciesSettings($currenciesSettingsDtos);
        }catch(Exception | TypeError $e){
            $this->app->logExceptionWasThrown($e);

            $message = $this->app->translator->translate('messages.general.internalServerError');
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return CallStatusDTO
     * @throws Exception
     */
    public function handleFinancesCurrencyForm(Request $request): CallStatusDTO {
        $currencyTypeForm = $this->createForm(CurrencyType::class);
        $currencyTypeForm->handleRequest($request);

        $callStatusDto = new CallStatusDTO();
        if( $currencyTypeForm->isSubmitted() && !$currencyTypeForm->isValid() ){
            $callStatusDto->setFailureReason(CallStatusDTO::KEY_FAILURE_REASON_FORM_VALIDATION);
            return $callStatusDto;
        }

        if( !$currencyTypeForm->isSubmitted() ) {
            $callStatusDto->setCode(200);
            return $callStatusDto;
        }

        $formData   = $currencyTypeForm->getData();
        $name       = $formData[SettingsCurrencyDTO::KEY_NAME]       ?? "";
        $symbol     = $formData[SettingsCurrencyDTO::KEY_SYMBOL]     ?? "";
        $multiplier = $formData[SettingsCurrencyDTO::KEY_MULTIPLIER] ?? "";
        $isDefault  = $formData[SettingsCurrencyDTO::KEY_IS_DEFAULT] ?? "";

        $settingsCurrencyDto = new SettingsCurrencyDTO();
        $settingsCurrencyDto->setName($name);
        $settingsCurrencyDto->setSymbol($symbol);
        $settingsCurrencyDto->setMultiplier($multiplier);
        $settingsCurrencyDto->setIsDefault($isDefault);

        $currenciesSettingsDtosInDb = $this->app->settings->settingsLoader->getCurrenciesDtosForSettingsFinances();

        // handle adding currency with the same name
        foreach( $currenciesSettingsDtosInDb as $index => $currencySettingDtoFromDb ){
            if( $currencySettingDtoFromDb->getName() === $settingsCurrencyDto->getName() ){
                $message = $this->app->translator->translate("settings.finances.type.messages.currencyWithThisNameAlreadyExist");
                $callStatusDto->setCode(400);
                $callStatusDto->setMessage($message);
                return $callStatusDto;
            }
        }

        // handle checking if default value already exist and if so then unset all and set new
        if( $isDefault ){
            $arrayIndexOfUpdatedSetting = null;

            try{

                foreach( $currenciesSettingsDtosInDb as $index => &$currencySettingDtoFromDb ){
                    $currencySettingDtoFromDb->setIsDefault(false);
                }

                $currenciesSettingsDtosInDb[] = $settingsCurrencyDto;
                $this->app->settings->settingsSaver->saveFinancesSettingsForCurrenciesSettings($currenciesSettingsDtosInDb);

                $callStatusDto->setCode(200);
                $callStatusDto->setIsSuccess(true);
            }catch(Exception $e){
                $message = $this->app->translator->translate("settings.finances.type.messages.couldNotHandleAddingNewCurrencyBeingDefaultValue");
                $callStatusDto->setCode(500);
                $callStatusDto->setMessage($message);
            }
        }

        // just add new record
        if( !$isDefault ){

            $settingValidationDto = $this->controllers->getSettingsFinancesController()->addCurrencyToFinancesCurrencySettings($settingsCurrencyDto);

            $callStatusDto->setIsSuccess($settingValidationDto->isValid());

            if( !$settingValidationDto->isValid() ){
                $callStatusDto->setMessage($settingValidationDto->getMessage());
                $callStatusDto->setCode(400);
            }else {
                $callStatusDto->setCode(200);
            }
        }

        return $callStatusDto;
    }

}