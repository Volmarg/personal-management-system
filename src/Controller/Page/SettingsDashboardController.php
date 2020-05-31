<?php

namespace App\Controller\Page;

use App\DTO\Settings\Dashboard\SettingsWidgetSettingsDTO;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDTO;
use App\DTO\Settings\SettingsDashboardDTO;
use App\Services\Settings\SettingsLoader;
use App\Services\Settings\SettingsSaver;
use App\Services\Core\Translator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsDashboardController extends AbstractController {

    const TWIG_DASHBOARD_SETTINGS_TEMPLATE = 'page-elements/settings/components/dashboard-settings.html.twig' ;

    #Info: this must be equivalent to the template name modules/my-dashboard-widgets
    const DASHBOARD_WIDGET_NAME_GOALS_PROGRESS      = 'my-goals';
    const DASHBOARD_WIDGET_NAME_GOALS_PAYMENTS      = 'my-goals-payments';
    const DASHBOARD_WIDGET_NAME_INCOMING_SCHEDULES  = 'incoming-schedules';
    const DASHBOARD_WIDGET_NAME_PENDING_ISSUES      = 'pending-issues';

    const ALL_DASHBOARD_WIDGETS_NAMES = [
        self::DASHBOARD_WIDGET_NAME_GOALS_PROGRESS,
        self::DASHBOARD_WIDGET_NAME_GOALS_PAYMENTS,
        self::DASHBOARD_WIDGET_NAME_INCOMING_SCHEDULES,
        self::DASHBOARD_WIDGET_NAME_PENDING_ISSUES,
    ];

    /**
     * @var \App\Services\Core\Translator $translator
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
     * Returns array of widgets names with their translations
     * @param Translator $translator
     * @return array
     * 
     */
    public static function getDashboardWidgetsNames(Translator $translator):array {

        $dashboard_widgets_names = [];

        foreach( self::ALL_DASHBOARD_WIDGETS_NAMES as $widget_name ){
            $dashboard_widgets_names[$widget_name] = $translator->translate('dashboard.widgets.' . $widget_name . '.label');
        }

        return $dashboard_widgets_names;
    }

    /**
     * Returns array of widgets names with initial visibilities
     * @param bool $all_visible
     * @return array
     */
    public static function getDashboardWidgetsInitialVisibility($all_visible = true){
        $dashboard_widgets_visibility = [];

        foreach( self::ALL_DASHBOARD_WIDGETS_NAMES as $widget_name ){
            $dashboard_widgets_visibility[$widget_name] = $all_visible;
        }

        return $dashboard_widgets_visibility;
    }

    /**
     * Builds array of widgets visibilities dto
     * @param bool $all_visible
     * @return array
     */
    public static function buildArrayOfWidgetsVisibilityDtoForInitialVisibility($all_visible = true){

        $array_of_widgets_visibility_dto = [];


        foreach( self::ALL_DASHBOARD_WIDGETS_NAMES as $widget_name ){

            $settings_widget_visibility_dto = new SettingsWidgetVisibilityDTO();
            $settings_widget_visibility_dto->setName($widget_name);
            $settings_widget_visibility_dto->setIsVisible($all_visible);

            $array_of_widgets_visibility_dto[] = $settings_widget_visibility_dto;
        }

        return $array_of_widgets_visibility_dto;
    }

    /**
     * This function will build dashboard settings dto based on supplied data, if some is missing then default values will be used
     * @param array|null $array_of_widgets_visibility_dto
     * @return SettingsDashboardDTO
     * @throws Exception
     */
    public static function buildDashboardSettingsDto(?array $array_of_widgets_visibility_dto = null): SettingsDashboardDTO{

        if( empty($array_of_widgets_visibility_dto) ){
            $array_of_widgets_visibility_dto   = [];
            $array_of_widgets_visibility_dto[] = new SettingsWidgetVisibilityDTO();
        }

        $dashboard_widgets_settings_dto = new SettingsWidgetSettingsDTO();
        $dashboard_widgets_settings_dto->setWidgetVisibility($array_of_widgets_visibility_dto);

        $dashboard_settings_dto = new SettingsDashboardDTO();
        $dashboard_settings_dto->setWidgetSettings($dashboard_widgets_settings_dto);

        return $dashboard_settings_dto;
    }
}
