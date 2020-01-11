<?php

namespace App\Controller\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController {

    const TWIG_SETTINGS_TEMPLATE = 'page-elements/settings/layout.html.twig' ;

    /**
     * @var SettingsViewController $settings_view_controller
     */
    private $settings_view_controller;

    /**
     * @var SettingsDashboardController $settings_dashboard_controller
     */
    private $settings_dashboard_controller;

    /**
     * @var SettingsFinancesController $settings_finances_controller
     */
    private $settings_finances_controller;

    /**
     * SettingsController constructor.
     * @param SettingsDashboardController $settings_dashboard_controller
     * @param SettingsViewController $settings_view_controller
     * @param SettingsFinancesController $settings_finances_controller
     */
    public function __construct(
        SettingsDashboardController $settings_dashboard_controller,
        SettingsViewController      $settings_view_controller,
        SettingsFinancesController  $settings_finances_controller
    ) {
        $this->settings_dashboard_controller = $settings_dashboard_controller;
        $this->settings_view_controller      = $settings_view_controller;
        $this->settings_finances_controller  = $settings_finances_controller;
    }

    /**
     * @Route("/page-settings", name="page-settings")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function display(Request $request) {
        $this->handleForms($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->settings_view_controller->renderSettingsTemplate(false);
        }
        return $this->settings_view_controller->renderSettingsTemplate(true);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    private function handleForms(Request $request){
        $this->settings_finances_controller->handleFinancesCurrencyForm($request);
    }

}
