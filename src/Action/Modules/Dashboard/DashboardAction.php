<?php

namespace App\Action\Modules\Dashboard;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\DTO\Settings\SettingsDashboardDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardAction extends AbstractController {

    const INCOMING_SCHEDULES_WIDGET_MAX_PER_PAGE = 10;
    const INCOMING_SCHEDULES_WIDGET_MAX_PAGES    = 5;

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/dashboard", name="dashboard")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent  = $this->renderTemplate( true)->getContent();

        $ajaxResponse = new AjaxResponse("",$templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getDashboardPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     * @throws Exception
     */
    protected function renderTemplate($ajaxRender = false): Response
    {
        $dashboardSettings               = $this->app->settings->settingsLoader->getSettingsForDashboard();
        $dashboardWidgetsVisibilityDtos  = null;

        if( !empty($dashboardSettings) ){
            $dashboardSettingsJson            = $dashboardSettings->getValue();
            $dashboardSettingsDto             = SettingsDashboardDTO::fromJson($dashboardSettingsJson);
            $dashboardWidgetsVisibilityDtos   = $dashboardSettingsDto->getWidgetSettings()->getWidgetsVisibility();
        }

        $maxResultsForIncomingSchedulesWidget = self::INCOMING_SCHEDULES_WIDGET_MAX_PAGES * self::INCOMING_SCHEDULES_WIDGET_MAX_PER_PAGE;

        $schedules     = $this->controllers->getDashboardController()->getIncomingSchedulesInformation($maxResultsForIncomingSchedulesWidget);
        $allTodo       = $this->controllers->getDashboardController()->getGoalsTodoForWidget();
        $goalsPayments = $this->controllers->getDashboardController()->getGoalsPayments();

        $pendingIssues   = $this->controllers->getDashboardController()->getPendingIssues();
        $issuesCardsDtos = $this->controllers->getMyIssuesController()->buildIssuesCardsDtosFromIssues($pendingIssues);

        $schedulesForPages = array_chunk($schedules, self::INCOMING_SCHEDULES_WIDGET_MAX_PER_PAGE);

        $data = [
            'dashboard_widgets_visibility_dtos' => $dashboardWidgetsVisibilityDtos,
            'schedules_for_pages'               => $schedulesForPages,
            'all_todo'                          => $allTodo,
            'goals_payments'                    => $goalsPayments,
            'issues_cards_dtos'                 => $issuesCardsDtos,
            'ajax_render'                       => $ajaxRender,
            'page_title'                        => $this->getDashboardPageTitle(),
        ];

        return $this->render("modules/my-dashboard/dashboard.html.twig", $data);
    }

    /**
     * Will return dashboard page title
     *
     * @return string
     */
    private function getDashboardPageTitle(): string
    {
        return $this->app->translator->translate('dashboardModule.title');
    }

}