<?php

namespace App\Controller\Page;

use App\Controller\Utils\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

class SettingsDashboardController extends AbstractController {

    const TWIG_DASHBOARD_SETTINGS_TEMPLATE = 'page-elements/settings/components/dashboard-settings.html.twig' ;

    const DASHBOARD_WIDGET_NAME_GOALS_PROGRESS = 'dashboard_widget_goals_progress';
    const DASHBOARD_WIDGET_NAME_GOALS_PAYMENTS = 'dashboard_widget_goals_payments';
    const DASHBOARD_WIDGET_NAME_CAR_SCHEDULES  = 'dashboard_widget_car_schedules';

    const KEY_WIDGET_NAME    = 'widget_name';
    const KEY_DISPLAY_WIDGET = 'display_widget';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
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


}
