<?php

namespace App\Controller\Modules\Dashboard;

use App\Controller\Utils\Application;
use App\DTO\Settings\SettingsDashboardDTO;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller {

    const CAR_SCHEDULE_MONTHS_INTERVAL = 2;

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
     */
    public function display(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }
        return $this->renderTemplate(true);
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

        $car_schedules              = $this->getCarSchedulesForWidget();
        $goals                      = $this->getGoalsForWidget();
        $goals_payments             = $this->getGoalsPayments();

        $data = [
            'dashboard_widgets_visibility_dtos'  => $dashboard_widgets_visibility_dtos,
            'incomingCarSchedules'               => $car_schedules,
            'goals'                              => $goals,
            'goals_payments'                     => $goals_payments,
            'ajax_render'                        => $ajax_render,
        ];

        return $this->render("modules/my-dashboard/dashboard.html.twig", $data);
    }

    private function getCarSchedulesForWidget() {
        return $this->app->repositories->myCarRepository->getIncomingCarSchedulesInMonths(static::CAR_SCHEDULE_MONTHS_INTERVAL);
    }

    # todo: at refactor - make widgets repo and move both there
    private function getGoalsForWidget(){
        return $this->app->repositories->myGoalsRepository->findBy([
            'displayOnDashboard' => 1,
            'deleted'            => 0
        ]);
    }

    private function getGoalsPayments(){
        return $this->app->repositories->myGoalsPaymentsRepository->findBy([
            'displayOnDashboard' => 1,
            'deleted'            => 0
        ]);
    }

}
