<?php

namespace App\Controller\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController {

    const TWIG_SETTINGS_TEMPLATE = 'page-elements/settings/layout.html.twig' ;

    const KEY_DASHBOARD_SETTINGS = 'dashboard';

    /**
     * @var SettingsViewController $settings_view_controller
     */
    private $settings_view_controller;

    /**
     * @var SettingsDashboardController $settings_dashboard_controller
     */
    private $settings_dashboard_controller;

    /**
     * SettingsController constructor.
     * @param SettingsDashboardController $settings_dashboard_controller
     * @param SettingsViewController $settings_view_controller
     * @throws \Exception
     */
    public function __construct(SettingsDashboardController $settings_dashboard_controller, SettingsViewController $settings_view_controller) {
        $this->settings_dashboard_controller = $settings_dashboard_controller;
        $this->settings_view_controller = $settings_view_controller;
    }

    /**
     * @Route("/page-settings", name="page-settings")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function display(Request $request) {
        if (!$request->isXmlHttpRequest()) {
            return $this->settings_view_controller->renderSettingsTemplate(false);
        }
        return $this->settings_view_controller->renderSettingsTemplate(true);
    }

}
