<?php

namespace App\Controller\Page;

use App\Controller\Utils\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class SettingsDashboardController extends AbstractController {

    const TWIG_SETTINGS_PAGE = '' ;

    const DASHBOARD_WIDGET_NAME_GOALS_PROGRESS = 'dashboard_widget_goals_progress';
    const DASHBOARD_WIDGET_NAME_GOALS_PAYMENTS = 'dashboard_widget_goals_payments';
    const DASHBOARD_WIDGET_NAME_CAR_SCHEDULES  = 'dashboard_widget_car_schedules';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * @Route("/page-settings", name="page-settings")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }
        return $this->renderTemplate(true);
    }

    protected function renderTemplate($ajax_render = false) {

       // render settings page here
        return $this->render(self::TWIG_SETTINGS_PAGE);

    }


}
