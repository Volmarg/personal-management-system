<?php

namespace App\Action\Page;

use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsDashboardAction extends AbstractController {

    const KEY_ALL_ROWS_DATA = 'all_rows_data';

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
     * In this case it's not single row update but entire setting string
     * So the data passed in is not single row but all rows in table
     * It's important to understand that import is done for whole setting name record
     * @Route("/api/settings-dashboard/update-widgets-visibility", name="settings_dashboard_update_widgets_visibility", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function updateWidgetsVisibility(Request $request){

        if (!$request->request->has(self::KEY_ALL_ROWS_DATA)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ALL_ROWS_DATA;
            throw new Exception($message);
        }

        $all_rows_data                      = $request->request->get(self::KEY_ALL_ROWS_DATA);
        $widgets_visibilities_settings_dtos = [];

        foreach($all_rows_data as $row_data){

            if( !array_key_exists(SettingsWidgetVisibilityDTO::KEY_IS_VISIBLE, $row_data)){
                $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsWidgetVisibilityDTO::KEY_IS_VISIBLE;
                throw new Exception($message);
            }

            if( !array_key_exists(SettingsWidgetVisibilityDTO::KEY_NAME, $row_data)){
                $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsWidgetVisibilityDTO::KEY_NAME;
                throw new Exception($message);
            }

            $is_visible = filter_var($row_data[SettingsWidgetVisibilityDTO::KEY_IS_VISIBLE], FILTER_VALIDATE_BOOLEAN);;
            $name       = trim($row_data[SettingsWidgetVisibilityDTO::KEY_NAME]);

            $widgets_visibility_settings_dto = new SettingsWidgetVisibilityDTO();
            $widgets_visibility_settings_dto->setName($name);
            $widgets_visibility_settings_dto->setIsVisible($is_visible);

            $widgets_visibilities_settings_dtos[] = $widgets_visibility_settings_dto;
        }

        $this->app->settings->settings_saver->saveSettingsForDashboardWidgetsVisibility($widgets_visibilities_settings_dtos);

        return $this->settings_view_action->renderSettingsTemplate(false);
    }

}