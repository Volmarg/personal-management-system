<?php

namespace App\Action\Page;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\DTO\Settings\Lock\Subsettings\SettingsModuleLockDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsModuleAction extends AbstractController {

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
     * Handles updating settings of module - lock
     * In this case it's not single row update but entire setting string
     * So the data passed in is not single row but all rows in table
     * It's important to understand that import is done for whole setting name record
     *
     * @Route("/api/settings-module/update-lock", name="settings_module_update_module_lock", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function updateModuleLock(Request $request): Response
    {

        if (!$request->request->has(self::KEY_ALL_ROWS_DATA)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ALL_ROWS_DATA;
            $this->app->logger->warning($message);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        $allRowsData            = $request->request->get(self::KEY_ALL_ROWS_DATA);
        $settingsModuleLockDtos = [];

        foreach($allRowsData as $rowData){

            if( !array_key_exists(SettingsModuleLockDTO::KEY_IS_LOCKED, $rowData)){
                $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsModuleLockDTO::KEY_IS_LOCKED;
                $this->app->logger->warning($message);
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
            }

            if( !array_key_exists(SettingsModuleLockDTO::KEY_NAME, $rowData)){
                $message = $this->app->translator->translate('responses.general.arrayInResponseIsMissingParameterNamed') . SettingsModuleLockDTO::KEY_NAME;
                $this->app->logger->warning($message);
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
            }

            $isLocked = filter_var($rowData[SettingsModuleLockDTO::KEY_IS_LOCKED], FILTER_VALIDATE_BOOLEAN);;
            $name      = trim($rowData[SettingsModuleLockDTO::KEY_NAME]);

            $settingsModuleLockDto = new SettingsModuleLockDTO();
            $settingsModuleLockDto->setName($name);
            $settingsModuleLockDto->setLocked($isLocked);

            $settingsModuleLockDtos[] = $settingsModuleLockDto;
        }

        $this->app->settings->settingsSaver->saveSettingsForModulesLock($settingsModuleLockDtos);

        return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_OK, "", "");
    }

}