<?php

namespace App\Controller\Page;

use App\DTO\Settings\Lock\SettingsModulesDTO;
use App\DTO\Settings\Lock\Subsettings\SettingsModuleLockDTO;
use App\Repository\SettingRepository;
use App\Services\Settings\SettingsLoader;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsLockModuleController extends AbstractController {

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
        $moduleSettings = $this->settingRepository->getSettingByName(SettingsLoader::SETTING_NAME_MODULES);
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
}
