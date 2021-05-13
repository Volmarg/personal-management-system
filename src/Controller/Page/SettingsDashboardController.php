<?php

namespace App\Controller\Page;

use App\Controller\Modules\ModulesController;
use App\DTO\Settings\Dashboard\SettingsWidgetSettingsDTO;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDTO;
use App\DTO\Settings\SettingsDashboardDTO;
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
     * @var SettingsLockModuleController $settingsLockModuleController
     */
    private SettingsLockModuleController $settingsLockModuleController;

    /**
     * SettingsDashboardController constructor.
     *
     * @param Translator $translator
     * @param SettingsLockModuleController $settingsLockModuleController
     */
    public function __construct(Translator $translator, SettingsLockModuleController $settingsLockModuleController) {
        $this->settingsLockModuleController = $settingsLockModuleController;
        $this->translator                   = $translator;
    }

    /**
     * Returns array of widgets names with their translations
     * @param Translator $translator
     * @return array
     * 
     */
    public function getDashboardWidgetsNames(Translator $translator): array {

        $dashboardWidgetsNames = [];

        foreach( self::ALL_DASHBOARD_WIDGETS_NAMES as $widgetName ){
            $dashboardWidgetsNames[$widgetName] = $translator->translate('dashboard.widgets.' . $widgetName . '.label');
        }

        return $dashboardWidgetsNames;
    }

    /**
     * Builds array of widgets visibilities dto
     * @param bool $allVisible
     * @return array
     */
    public static function buildArrayOfWidgetsVisibilityDtoForInitialVisibility($allVisible = true){

        $arrayOfWidgetsVisibilityDto = [];
        foreach( self::ALL_DASHBOARD_WIDGETS_NAMES as $widgetName ){

            $settingsWidgetVisibilityDto = new SettingsWidgetVisibilityDTO();
            $settingsWidgetVisibilityDto->setName($widgetName);
            $settingsWidgetVisibilityDto->setIsVisible($allVisible);

            $arrayOfWidgetsVisibilityDto[] = $settingsWidgetVisibilityDto;
        }

        return $arrayOfWidgetsVisibilityDto;
    }

    /**
     * This function will build dashboard settings dto based on supplied data, if some is missing then default values will be used
     * @param array|null $arrayOfWidgetsVisibilityDto
     * @return SettingsDashboardDTO
     * @throws Exception
     */
    public static function buildDashboardSettingsDto(?array $arrayOfWidgetsVisibilityDto = null): SettingsDashboardDTO{

        if( empty($arrayOfWidgetsVisibilityDto) ){
            $arrayOfWidgetsVisibilityDto   = [];
            $arrayOfWidgetsVisibilityDto[] = new SettingsWidgetVisibilityDTO();
        }

        $dashboardWidgetsSettingsDto = new SettingsWidgetSettingsDTO();
        $dashboardWidgetsSettingsDto->setWidgetVisibility($arrayOfWidgetsVisibilityDto);

        $dashboardSettingsDto = new SettingsDashboardDTO();
        $dashboardSettingsDto->setWidgetSettings($dashboardWidgetsSettingsDto);

        return $dashboardSettingsDto;
    }
}
