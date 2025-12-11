<?php

namespace App\Services\Settings;

use App\DTO\Settings\Lock\SettingsModulesDTO;
use App\DTO\Settings\Lock\Subsettings\SettingsModuleLockDTO;
use App\Repository\SettingRepository;
use App\Services\Module\ModulesService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsLockModuleService extends AbstractController {

    /**
     * @var SettingsModuleLockDTO[] $settingsModuleLockDtos
     */
    private array $settingsModuleLockDtos;

    public function getSettingsModuleLockDtos(): array
    {
        return $this->settingsModuleLockDtos;
    }

    /**
     * Info: this method is called in `services.yaml` so that it will be set just once instead of calling DB over and over again
     * @throws Exception
     */
    public function initializeSettingsModuleLockDtos(): void
    {
        $allModules = array_combine(ModulesService::ALL_MODULES, ModulesService::ALL_MODULES);
        $allModulesMap = array_map(function(string $moduleName){
            return new SettingsModuleLockDTO($moduleName, false);
        }, $allModules);

        $moduleSettings = $this->settingRepository->getSettingByName(SettingsLoader::SETTING_NAME_MODULES);
        if (empty($moduleSettings)) {
            $this->settingsModuleLockDtos = $this->sortLocks($allModulesMap);
            return;
        }
        $settingsModuleLockDtosDataArray = json_decode($moduleSettings->getValue(), true);
        $dbSavedLocks = array_map(
            fn(array $dtoDataArray) => SettingsModuleLockDTO::fromArray($dtoDataArray),
            $settingsModuleLockDtosDataArray[SettingsModulesDTO::KEY_MODULE_LOCK_SETTINGS]
        );

        foreach ($dbSavedLocks as $dbSavedLock) {
            $this->settingsModuleLockDtos[$dbSavedLock->getName()] = $dbSavedLock;
        }

        foreach ($allModulesMap as $moduleLock) {
            if (array_key_exists($moduleLock->getName(), $this->settingsModuleLockDtos)) {
                continue;
            }

            $this->settingsModuleLockDtos[$moduleLock->getName()] = $moduleLock;
        }

        $this->settingsModuleLockDtos = $this->sortLocks($this->settingsModuleLockDtos);
    }

    /**
     * @param SettingRepository $settingRepository
     */
    public function __construct(
        private readonly SettingRepository $settingRepository
    ) {
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

    /**
     * @param SettingsModuleLockDTO[] $locks
     *
     * @return array
     */
    private function sortLocks(array $locks): array
    {
        $sorted = [];
        foreach (ModulesService::ALL_MODULES as $moduleName) {
            if (array_key_exists($moduleName, $locks)) {
                $sorted[] = $locks[$moduleName];
            }
        }

        return $sorted;
    }
}
