<?php

namespace App\Action\Page;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\DTO\CallStatusDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsAction extends AbstractController {

    const TWIG_SETTINGS_TEMPLATE  = 'page-elements/settings/layout.html.twig' ;

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    /**
     * @var SettingsFinancesAction $settings_finances_action
     */
    private $settings_finances_action;

    /**
     * @var SettingsViewAction $settings_view_action
     */
    private $settings_view_action;

    public function __construct(
        Controllers             $controllers,
        Application             $app,
        SettingsFinancesAction  $settings_finances_action,
        SettingsViewAction      $settings_view_action
    ) {
        $this->app = $app;
        $this->controllers = $controllers;
        $this->settings_view_action = $settings_view_action;
        $this->settings_finances_action = $settings_finances_action;
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
            return $this->settings_view_action->renderSettingsTemplate(false);
        }

        $template = $this->settings_view_action->renderSettingsTemplate(true)->getContent();
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
        $call_status_dto = $this->settings_finances_action->handleFinancesCurrencyForm($request);
        return $call_status_dto;
    }

}