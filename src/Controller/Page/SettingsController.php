<?php

namespace App\Controller\Page;

use App\Controller\Utils\Application;
use App\DTO\Settings\SettingsDashboardDTO;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController {

    const TWIG_SETTINGS_PAGE = '' ;

    const SETTING_NAME_DASHBOARD = 'dashboard';

    /**
     * @var Application
     */
    private $app;

    /**
     * @var SettingsDashboardDTO
     */
    private $settings_dashboard_dto;

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

    /**
     * This function will use the db json and build dto
     * @throws \Exception
     */
    public function setSettingsDashboardDto(){
        $setting_json = $this->fetchSettingsDashboard();
        $dto = SettingsDashboardDTO::fromJson($setting_json);
        $this->settings_dashboard_dto = $dto;
    }

    /**
     * This function will fetch json from db
     * @throws DBALException
     */
    public function fetchSettingsDashboard(){
        $setting_json = $this->app->repositories->settingRepository->fetchSettingsForDashboard();
        return $setting_json;
    }

}
