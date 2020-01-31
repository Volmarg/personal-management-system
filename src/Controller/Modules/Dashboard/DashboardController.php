<?php

namespace App\Controller\Modules\Dashboard;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\DTO\Settings\SettingsDashboardDTO;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller {

    const CAR_SCHEDULE_DAYS_INTERVAL = 60;

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
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
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
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

        $schedules      = $this->getIncomingSchedules();
        $goals          = $this->getGoalsForWidget();
        $goals_payments = $this->getGoalsPayments();

        $data = [
            'dashboard_widgets_visibility_dtos'  => $dashboard_widgets_visibility_dtos,
            'schedules'                          => $schedules,
            'goals'                              => $goals,
            'goals_payments'                     => $goals_payments,
            'ajax_render'                        => $ajax_render,
        ];

        return $this->render("modules/my-dashboard/dashboard.html.twig", $data);
    }

    private function getIncomingSchedules() {
        return $this->app->repositories->myScheduleRepository->getIncomingSchedulesInDays(static::CAR_SCHEDULE_DAYS_INTERVAL);
    }

    private function getGoalsForWidget(){
        return $this->app->repositories->myGoalsRepository->getGoalsForWidget();
    }

    private function getGoalsPayments(){
        return $this->app->repositories->myGoalsPaymentsRepository->getGoalsPayments();
    }

}
