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
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    /**
     * @var SettingsFinancesAction $settingsFinancesAction
     */
    private SettingsFinancesAction $settingsFinancesAction;

    /**
     * @var SettingsViewAction $settingsViewAction
     */
    private SettingsViewAction $settingsViewAction;

    public function __construct(
        Controllers             $controllers,
        Application             $app,
        SettingsFinancesAction  $settingsFinancesAction,
        SettingsViewAction      $settingsViewAction
    ) {
        $this->app                    = $app;
        $this->controllers            = $controllers;
        $this->settingsViewAction     = $settingsViewAction;
        $this->settingsFinancesAction = $settingsFinancesAction;
    }

    /**
     * @Route("/page-settings", name="page-settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $callStatusDto = $this->handleForms($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->settingsViewAction->renderSettingsTemplate();
        }

        $code     = $callStatusDto->getCode();
        $message  = $callStatusDto->getMessage();

        $response = AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
        return $response;
    }

    /**
     * @param Request $request
     * @return CallStatusDTO
     * @throws Exception
     */
    private function handleForms(Request $request): CallStatusDTO
    {
        $callStatusDto = $this->settingsFinancesAction->handleFinancesCurrencyForm($request);
        return $callStatusDto;
    }

}