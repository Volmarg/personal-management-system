<?php

namespace App\Controller\Page;

use App\Controller\Utils\AjaxResponse;
use App\DTO\CallStatusDTO;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\Entity\Setting;
use App\Services\Settings\SettingsLoader;
use App\Services\Translator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController {

    const TWIG_SETTINGS_TEMPLATE  = 'page-elements/settings/layout.html.twig' ;
    const KEY_BEFORE_UPDATE_STATE = "before_update_state";

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
     * @var SettingsLoader $settings_loader
     */
    private $settings_loader;

    /**
     * @var Translator $translator
     */
    private $translator;

    /**
     * SettingsController constructor.
     * @param SettingsDashboardController $settings_dashboard_controller
     * @param SettingsViewController $settings_view_controller
     * @param SettingsFinancesController $settings_finances_controller
     * @param SettingsLoader $settings_loader
     * @param Translator $translator
     */
    public function __construct(
        SettingsDashboardController $settings_dashboard_controller,
        SettingsViewController      $settings_view_controller,
        SettingsFinancesController  $settings_finances_controller,
        SettingsLoader              $settings_loader,
        Translator                  $translator
    ) {
        $this->settings_dashboard_controller = $settings_dashboard_controller;
        $this->settings_view_controller      = $settings_view_controller;
        $this->settings_finances_controller  = $settings_finances_controller;
        $this->settings_loader               = $settings_loader;
        $this->translator                    = $translator;
    }

    /**
     * @Route("/page-settings", name="page-settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response {
        $call_status_dto = $this->handleForms($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->settings_view_controller->renderSettingsTemplate(false);
        }

        $template = $this->settings_view_controller->renderSettingsTemplate(true)->getContent();
        $code     = $call_status_dto->getCode();
        $message  = $call_status_dto->getMessage();

        $response = AjaxResponse::buildResponseForAjaxCall($code, $message, $template);
        return $response;
    }

    /**
     * @param Request $request
     * @return CallStatusDTO
     * @throws Exception
     */
    private function handleForms(Request $request): CallStatusDTO{
        $call_status_dto = $this->settings_finances_controller->handleFinancesCurrencyForm($request);

        return $call_status_dto;
    }

    /**
     * @param Setting|null $setting
     * @return SettingsFinancesDTO|null
     * @throws Exception
     */
    public static function buildFinancesSettingsDtoFromSetting(?Setting $setting): ?SettingsFinancesDTO {

        if( empty($setting) ){
            return null;
        }

        $setting_name = $setting->getName();

        if( SettingsLoader::SETTING_NAME_FINANCES !== $setting_name ){
            throw new Exception("Incorrect setting was provided: " . $setting_name);
        }

        $settings_finances_json = $setting->getValue();
        $settings_finances_dto  = SettingsFinancesDTO::fromJson($settings_finances_json);

        return $settings_finances_dto;
    }

}
