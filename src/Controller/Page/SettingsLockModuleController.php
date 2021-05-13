<?php

namespace App\Controller\Page;

use App\Controller\Core\Repositories;
use App\Controller\Modules\ModulesController;
use App\DTO\Settings\Lock\SettingsModulesDTO;
use App\DTO\Settings\Lock\Subsettings\SettingsModuleLockDTO;
use App\Services\Settings\SettingsLoader;
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
     * @var Repositories $repositories
     */
    private Repositories $repositories;

    /**
     * @var SettingsModuleLockDTO[] $settingsModuleLockDtos
     */
    private array $settingsModuleLockDtos;

    /**
     * Info: this method is called in `services.yaml` so that it will be set just once instead of calling DB over and over again
     * @throws Exception
     */
    public function initializeSettingsModuleLockDtos(): void
    {
        $moduleSettings = $this->repositories->settingRepository->getSettingByName(SettingsLoader::SETTING_NAME_MODULES);
        if( empty($moduleSettings) ){
            $this->settingsModuleLockDtos = [];
            return;
        }
        $settingsModuleLockDtosDataArray = json_decode($moduleSettings->getValue(), true);
        $this->settingsModuleLockDtos    = array_map(
            fn(array $dtoDataArray) => SettingsModuleLockDTO::fromArray($dtoDataArray),
            $settingsModuleLockDtosDataArray[SettingsModulesDTO::KEY_MODULE_LOCK_SETTINGS]
        );
    }

    /**
     * SettingsLockModuleController constructor.
     * @param Repositories $repositories
     */
    public function __construct(Repositories $repositories) {
        $this->repositories = $repositories;
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

    /**
     * Will check if the module is locked in the page settings or not
     *
     * @param string $moduleName
     * @return bool
     */
    public function isModuleLocked(string $moduleName): bool
    {
        foreach($this->settingsModuleLockDtos as $settingsModuleLockDto){

            if(
                    $settingsModuleLockDto->getName() === $moduleName
                &&  $settingsModuleLockDto->isLocked()
            ){
                return true;
            }
        }

        return false;
    }
}
