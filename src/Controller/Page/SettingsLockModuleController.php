<?php

namespace App\Controller\Page;

use App\Controller\Core\Application;
use App\Controller\Modules\ModulesController;
use App\DTO\Settings\Lock\SettingsModulesDTO;
use App\DTO\Settings\Lock\Subsettings\SettingsModuleLockDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsLockModuleController extends AbstractController {

    const TWIG_DASHBOARD_SETTINGS_TEMPLATE = 'page-elements/settings/components/lock/lock-module-settings.twig' ;

    const ALL_SUPPORTED_MODULES = [
        ModulesController::MODULE_NAME_ACHIEVEMENTS,
        ModulesController::MODULE_NAME_FILES,
        ModulesController::MODULE_NAME_GOALS,
        ModulesController::MODULE_NAME_TODO,
        ModulesController::MODULE_NAME_IMAGES,
        ModulesController::MODULE_NAME_VIDEO,
        ModulesController::MODULE_NAME_NOTES,
        ModulesController::MODULE_NAME_PASSWORDS,
        ModulesController::MODULE_NAME_ISSUES,
    ];

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * SettingsLockModuleController constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Builds array of module lock visibility settings
     * - can be used to initially prefill settings if there is nothing in db yet
     *
     * @param bool $allLocked
     * @return array
     */
    public static function buildArrayOfModulesLockDtosForInitialVisibility(bool $allLocked = false): array
    {

        $arrayOfModulesLockSettingDto = [];
        foreach( self::ALL_SUPPORTED_MODULES as $moduleName ){

            $moduleLockSetting = new SettingsModuleLockDTO();
            $moduleLockSetting->setName($moduleName);
            $moduleLockSetting->setLocked($allLocked);

            $arrayOfModulesLockSettingDto[] = $moduleLockSetting;
        }

        return $arrayOfModulesLockSettingDto;
    }

    /**
     * This function will build modules settings dto based on supplied data, if some is missing then default values will be used
     *
     * @param SettingsModuleLockDTO[]|null $arrayOfModulesLockDtos
     * @return SettingsModulesDTO
     * @throws Exception
     */
    public static function buildModulesSettingsDto(?array $arrayOfModulesLockDtos = null): SettingsModulesDTO
    {

        if( empty($arrayOfModulesLockDtos) ){
            $arrayOfModulesLockDtos   = [];
            $arrayOfModulesLockDtos[] = new SettingsModuleLockDTO();
        }

        $dashboardSettingsDto = new SettingsModulesDTO();
        $dashboardSettingsDto->setModuleLockDtos($arrayOfModulesLockDtos);

        return $dashboardSettingsDto;
    }
}
