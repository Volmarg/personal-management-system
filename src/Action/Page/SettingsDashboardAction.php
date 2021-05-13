<?php

namespace App\Action\Page;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsDashboardAction extends AbstractController {

    const KEY_ALL_ROWS_DATA = 'all_rows_data';

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Controllers $controllers, Application $app) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * Handles updating settings of dashboard - widgets visibility
     * In this case it's not single row update but entire setting string
     * So the data passed in is not single row but all rows in table
     * It's important to understand that import is done for whole setting name record
     *
     * @Route("/api/settings-dashboard/update-widgets-visibility", name="settings_dashboard_update_widgets_visibility", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function updateWidgetsVisibility(Request $request): Response
    {

        if (!$request->request->has(self::KEY_ALL_ROWS_DATA)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ALL_ROWS_DATA;
            $this->app->logger->warning($message);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        $allRowsData                     = $request->request->get(self::KEY_ALL_ROWS_DATA);
        $widgetsVisibilitiesSettingsDtos = [];

        foreach($allRowsData as $rowData){

            if( !array_key_exists(SettingsWidgetVisibilityDTO::KEY_IS_VISIBLE, $rowData)){
                $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsWidgetVisibilityDTO::KEY_IS_VISIBLE;
                $this->app->logger->warning($message);
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
            }

            if( !array_key_exists(SettingsWidgetVisibilityDTO::KEY_NAME, $rowData)){
                $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsWidgetVisibilityDTO::KEY_NAME;
                $this->app->logger->warning($message);
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
            }

            $isVisible = filter_var($rowData[SettingsWidgetVisibilityDTO::KEY_IS_VISIBLE], FILTER_VALIDATE_BOOLEAN);;
            $name      = trim($rowData[SettingsWidgetVisibilityDTO::KEY_NAME]);

            $widgetsVisibilitySettingsDto = new SettingsWidgetVisibilityDTO();
            $widgetsVisibilitySettingsDto->setName($name);
            $widgetsVisibilitySettingsDto->setIsVisible($isVisible);

            $widgetsVisibilitiesSettingsDtos[] = $widgetsVisibilitySettingsDto;
        }

        $this->app->settings->settingsSaver->saveSettingsForDashboardWidgetsVisibility($widgetsVisibilitiesSettingsDtos);

        return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_OK, "", "");
    }

}