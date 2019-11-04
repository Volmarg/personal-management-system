<?php

namespace App\Controller\Page;

use App\Controller\Utils\Application;
use App\DTO\Settings\SettingsDashboardDTO;
use App\Services\Settings\SettingsLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController {

    const TWIG_SETTINGS_TEMPLATE = 'page-elements/settings/layout.html.twig' ;

    const KEY_DASHBOARD_SETTINGS = 'dashboard';

    /**
     * @var Application
     */
    private $app;

    /**
     * @var SettingsDashboardDTO
     */
    private $settings_dashboard_dto;

    /**
     * @var SettingsLoader $settings_loader
     */
    private $settings_loader;

    /**
     * SettingsController constructor.
     * @param Application $app
     * @param SettingsLoader $settings_loader
     * @throws \Exception
     */
    public function __construct(Application $app, SettingsLoader $settings_loader) {
        $this->settings_loader        = $settings_loader;
        $this->app                    = $app;

        $this->settings_dashboard_dto = $this->buildSettingsDashboardDtoFromSettingsJsonInDb();

    }

    /**
     * @Route("/page-settings", name="page-settings")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function display(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsTemplate(false);
        }
        return $this->renderSettingsTemplate(true);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws \Exception
     */
    private function renderSettingsTemplate($ajax_render = false) {

        $dashboard_settings_view = $this->renderSettingsDashboardTemplate($ajax_render)->getContent();

        $data = [
            'ajax_render'             => $ajax_render,
            'dashboard_settings_view' => $dashboard_settings_view
        ];

        return $this->render(self::TWIG_SETTINGS_TEMPLATE, $data);

    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws \Exception
     */
    private function renderSettingsDashboardTemplate($ajax_render = false) {

        $dashboard_settings_dto         = $this->buildSettingsDashboardDtoFromSettingsJsonInDb();
        $widgets_visibility_settings    = $dashboard_settings_dto->getWidgetSettings()->getWidgetsVisibility();
        $widgets_names                  = SettingsDashboardController::getDashboardWidgetsNames($this->app);

        $data = [
            'ajax_render'                 => $ajax_render,
            "widgets_names"               => $widgets_names,
            "widgets_visibility_settings" => $widgets_visibility_settings
        ];

        return $this->render(SettingsDashboardController::TWIG_DASHBOARD_SETTINGS_TEMPLATE, $data);
    }


    /**
     * This function will use the db json and build dto
     * @throws \Exception
     */
    public function buildSettingsDashboardDtoFromSettingsJsonInDb():?SettingsDashboardDTO {
        $setting      = $this->settings_loader->getSettingsForDashboard();
        $setting_json = $setting->getValue();

        if( empty($setting_json) ){
            return null;
        }

        $dto = SettingsDashboardDTO::fromJson($setting_json);

        return $dto;
    }

}
