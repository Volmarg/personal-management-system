<?php

namespace App\Action\Page;

use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Page\SettingsDashboardController;
use App\DTO\Settings\SettingsDashboardDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SettingsViewAction extends AbstractController {

    const TWIG_SETTINGS_TEMPLATE = 'page-elements/settings/layout.html.twig' ;

    const KEY_DASHBOARD_SETTINGS = 'dashboard';

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;


    public function __construct(Controllers $controllers, Application $app) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws Exception
     */
    public function renderSettingsTemplate($ajax_render = false): Response
    {

        $dashboardSettingsView = $this->renderSettingsDashboardTemplate($ajax_render)->getContent();
        $financesSettingsView  = $this->renderSettingsFinancesTemplate($ajax_render)->getContent();

        $data = [
            'ajax_render'             => $ajax_render,
            'dashboard_settings_view' => $dashboardSettingsView,
            'finances_settings_view'  => $financesSettingsView,
        ];

        return $this->render(self::TWIG_SETTINGS_TEMPLATE, $data);
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     * @throws Exception
     */
    private function renderSettingsDashboardTemplate($ajaxRender = false): Response
    {

        $settingForDashboard = $this->app->settings->settingsLoader->getSettingsForDashboard();

        $areSettingsInDb = !empty($settingForDashboard);

        if( $areSettingsInDb ){
            $settingJson           = $settingForDashboard->getValue();
            $dashboardSettingsDto  = SettingsDashboardDTO::fromJson($settingJson);
        }else{
            $arrayOfWidgetsVisibilityDto = SettingsDashboardController::buildArrayOfWidgetsVisibilityDtoForInitialVisibility(true);
            $dashboardSettingsDto        = SettingsDashboardController::buildDashboardSettingsDto($arrayOfWidgetsVisibilityDto);
        }

        $widgetsVisibilitySettings = $dashboardSettingsDto->getWidgetSettings()->getWidgetsVisibility();
        $widgetsNames              = SettingsDashboardController::getDashboardWidgetsNames($this->app->translator);

        $data = [
            'ajax_render'                 => $ajaxRender,
            "widgets_names"               => $widgetsNames,
            "widgets_visibility_settings" => $widgetsVisibilitySettings
        ];

        return $this->render(SettingsDashboardController::TWIG_DASHBOARD_SETTINGS_TEMPLATE, $data);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws Exception
     */
    private function renderSettingsFinancesTemplate(bool $ajax_render = false): Response
    {
        $currenciesSettings = $this->app->settings->settingsLoader->getCurrenciesDtosForSettingsFinances();
        $currencyForm       = $this->app->forms->currencyTypeForm();

        $data = [
            'ajax_render'         => $ajax_render,
            "currencies_settings" => $currenciesSettings,
            'currency_form'       => $currencyForm->createView()
        ];

        return $this->render(SettingsFinancesAction::TWIG_FINANCES_SETTINGS_TEMPLATE, $data);
    }
}