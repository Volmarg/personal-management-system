<?php

namespace App\Controller\Page;

use App\Controller\Utils\Application;
use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\DTO\Settings\SettingsDashboardDTO;
use App\Form\Page\Settings\Finances\CurrencyType;
use App\Services\Settings\SettingsLoader;
use App\Services\Translator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class contains only the views rendering logic for settings
 * Class SettingsViewController
 * @package App\Controller\Page
 */
class SettingsViewController extends AbstractController {

    const TWIG_SETTINGS_TEMPLATE = 'page-elements/settings/layout.html.twig' ;

    const KEY_DASHBOARD_SETTINGS = 'dashboard';

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var SettingsLoader $settings_loader
     */
    private $settings_loader;

    /**
     * SettingsController constructor.
     * @param Translator $translator
     * @param SettingsLoader $settings_loader
     */
    public function __construct(Translator $translator, SettingsLoader $settings_loader) {
        $this->settings_loader = $settings_loader;
        $this->translator      = $translator;
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws Exception
     */
    public function renderSettingsTemplate($ajax_render = false) {

        $dashboard_settings_view = $this->renderSettingsDashboardTemplate($ajax_render)->getContent();
        $finances_settings_view  = $this->renderSettingsFinancesTemplate($ajax_render)->getContent();

        $data = [
            'ajax_render'             => $ajax_render,
            'dashboard_settings_view' => $dashboard_settings_view,
            'finances_settings_view'  => $finances_settings_view,
        ];

        return $this->render(self::TWIG_SETTINGS_TEMPLATE, $data);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws Exception
     */
    private function renderSettingsDashboardTemplate($ajax_render = false) {

        $setting_for_dashboard = $this->settings_loader->getSettingsForDashboard();

        $are_settings_in_db = !empty($setting_for_dashboard);

        if( $are_settings_in_db ){
            $setting_json            = $setting_for_dashboard->getValue();
            $dashboard_settings_dto  = SettingsDashboardDTO::fromJson($setting_json);
        }else{
            $array_of_widgets_visibility_dto = SettingsDashboardController::buildArrayOfWidgetsVisibilityDtoForInitialVisibility(true);
            $dashboard_settings_dto          = SettingsDashboardController::buildDashboardSettingsDto($array_of_widgets_visibility_dto);
        }

        $widgets_visibility_settings    = $dashboard_settings_dto->getWidgetSettings()->getWidgetsVisibility();
        $widgets_names                  = SettingsDashboardController::getDashboardWidgetsNames($this->translator);

        $data = [
            'ajax_render'                 => $ajax_render,
            "widgets_names"               => $widgets_names,
            "widgets_visibility_settings" => $widgets_visibility_settings
        ];

        return $this->render(SettingsDashboardController::TWIG_DASHBOARD_SETTINGS_TEMPLATE, $data);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws Exception
     */
    private function renderSettingsFinancesTemplate(bool $ajax_render = false): Response {
        $currencies_settings = $this->settings_loader->getCurrenciesDtosForSettingsFinances();
        $currency_form       = $this->createForm(CurrencyType::class);

        $data = [
            'ajax_render'         => $ajax_render,
            "currencies_settings" => $currencies_settings,
            'currency_form'       => $currency_form->createView()
        ];

        return $this->render(SettingsFinancesController::TWIG_FINANCES_SETTINGS_TEMPLATE, $data);
    }

}
