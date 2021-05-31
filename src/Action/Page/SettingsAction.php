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

        $template = $this->settingsViewAction->renderSettingsTemplate(true)->getContent();
        $code     = $callStatusDto->getCode();
        $message  = $callStatusDto->getMessage();

        $ajaxResponse = new AjaxResponse($message, $template);
        $ajaxResponse->setCode($code);
        $ajaxResponse->setPageTitle($this->settingsViewAction->getSettingsPageTitle());

        return $ajaxResponse->buildJsonResponse();
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