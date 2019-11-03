<?php

namespace App\Controller\Page;

use App\Controller\Utils\Application;
use App\DTO\Settings\Dashboard\SettingsWidgetSettingsDTO;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDTO;
use App\DTO\Settings\SettingsDashboardDTO;
use App\Services\Settings\SettingsSaver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsDashboardController extends AbstractController {

    const TWIG_DASHBOARD_SETTINGS_TEMPLATE = 'page-elements/settings/components/dashboard-settings.html.twig' ;

    const DASHBOARD_WIDGET_NAME_GOALS_PROGRESS = 'dashboard_widget_goals_progress';
    const DASHBOARD_WIDGET_NAME_GOALS_PAYMENTS = 'dashboard_widget_goals_payments';
    const DASHBOARD_WIDGET_NAME_CAR_SCHEDULES  = 'dashboard_widget_car_schedules';

    const KEY_ALL_ROWS_DATA = 'all_rows_data';

    /**
     * @var Application
     */
    private $app;

    /**
     * @var SettingsSaver $settings_saver
     */
    private $settings_saver;

    public function __construct(Application $app, SettingsSaver $settings_saver) {
        $this->settings_saver = $settings_saver;
        $this->app = $app;
    }

    public static function getDashboardWidgetsNames(Application $app):array {

        $dashboard_widgets = [
            self::DASHBOARD_WIDGET_NAME_CAR_SCHEDULES  => $app->translator->translate('dashboard.widgets.carSchedules.label'),
            self::DASHBOARD_WIDGET_NAME_GOALS_PAYMENTS => $app->translator->translate('dashboard.widgets.goalsPayments.label'),
            self::DASHBOARD_WIDGET_NAME_GOALS_PROGRESS => $app->translator->translate('dashboard.widgets.goalsProgress.label')
        ];

        return $dashboard_widgets;
    }

    /**
     * Handles updating settings of dashboard - widgets visibility
     * In this case it's not single row update but entire setting string
     * So the data passed in is not single row but all rows in table
     * It's important to understand that import is done for whole setting name record
     * @Route("/api/settings-dashboard/update-widgets-visibility", name="settings_dashboard_update_widgets_visibility", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function updateWidgetsVisibility(Request $request){

        if (!$request->request->has(self::KEY_ALL_ROWS_DATA)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ALL_ROWS_DATA;
            throw new \Exception($message);
        }

        $all_rows_data                      = $request->request->get(self::KEY_ALL_ROWS_DATA);
        $widgets_visibilities_settings_dtos = [];

        foreach($all_rows_data as $row_data){

            if( !array_key_exists(SettingsWidgetVisibilityDTO::KEY_IS_VISIBLE, $row_data)){
                $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsWidgetVisibilityDTO::KEY_IS_VISIBLE;
                throw new \Exception($message);
            }

            if( !array_key_exists(SettingsWidgetVisibilityDTO::KEY_NAME, $row_data)){
                $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsWidgetVisibilityDTO::KEY_NAME;
                throw new \Exception($message);
            }

            $is_visible = filter_var($row_data[SettingsWidgetVisibilityDTO::KEY_IS_VISIBLE], FILTER_VALIDATE_BOOLEAN);;
            $name       = trim($row_data[SettingsWidgetVisibilityDTO::KEY_NAME]);

            $widgets_visibility_settings_dto = new SettingsWidgetVisibilityDTO();
            $widgets_visibility_settings_dto->setName($name);
            $widgets_visibility_settings_dto->setIsVisible($is_visible);

            $widgets_visibilities_settings_dtos[] = $widgets_visibility_settings_dto;
        }

        $this->settings_saver->saveSettingsForDashboardWidgetsVisibility($widgets_visibilities_settings_dtos);

        //todo
        return '';
    }

    /**
     * This function will build dashboard settings dto based on supplied data, if some is missing then default values will be used
     * @param array|null $array_of_widgets_visibility_dto
     * @return SettingsDashboardDTO
     * @throws \Exception
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
