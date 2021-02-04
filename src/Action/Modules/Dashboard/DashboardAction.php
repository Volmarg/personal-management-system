<?php

namespace App\Action\Modules\Dashboard;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\DTO\Settings\SettingsDashboardDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardAction extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/dashboard", name="dashboard")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function display(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate( true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws \Exception
     */
    protected function renderTemplate($ajax_render = false) {

        $dashboard_settings                 = $this->app->settings->settings_loader->getSettingsForDashboard();
        $dashboard_widgets_visibility_dtos  = null;

        if( !empty($dashboard_settings) ){
            $dashboard_settings_json             = $dashboard_settings->getValue();
            $dashboard_settings_dto              = SettingsDashboardDTO::fromJson($dashboard_settings_json);
            $dashboard_widgets_visibility_dtos   = $dashboard_settings_dto->getWidgetSettings()->getWidgetsVisibility();
        }

        $schedules      = $this->controllers->getDashboardController()->getIncomingSchedulesInformation();
        $all_too        = $this->controllers->getDashboardController()->getGoalsTodoForWidget();
        $goals_payments = $this->controllers->getDashboardController()->getGoalsPayments();

        $pending_issues    = $this->controllers->getDashboardController()->getPendingIssues();
        $issues_cards_dtos = $this->controllers->getMyIssuesController()->buildIssuesCardsDtosFromIssues($pending_issues);

        $data = [
            'dashboard_widgets_visibility_dtos'  => $dashboard_widgets_visibility_dtos,
            'schedules'                          => $schedules,
            'all_todo'                           => $all_too,
            'goals_payments'                     => $goals_payments,
            'issues_cards_dtos'                  => $issues_cards_dtos,
            'ajax_render'                        => $ajax_render,
        ];

        return $this->render("modules/my-dashboard/dashboard.html.twig", $data);
    }

}