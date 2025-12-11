<?php

namespace App\Services\Settings;


use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDto;
use App\DTO\Settings\Finances\SettingsCurrencyDto;
use App\DTO\Settings\Finances\SettingsFinancesDto;
use App\DTO\Settings\Lock\SettingsModulesDTO;
use App\DTO\Settings\Notifications\ConfigDto;
use App\DTO\Settings\SettingNotificationDto;
use App\DTO\Settings\SettingsDashboardDto;
use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Class SettingsSaver
 * @package App\Services\Files
 */
class SettingsSaver {

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var SettingsLoader $settingsLoader
     */
    private $settingsLoader;

    /**
     * @param EntityManagerInterface $em
     * @param SettingsLoader $settingsLoader
     */
    public function __construct(
        EntityManagerInterface  $em,
        SettingsLoader          $settingsLoader

    ) {
        $this->em = $em;
        $this->settingsLoader = $settingsLoader;
    }

    /**
     * @param SettingsWidgetVisibilityDto[] $arrayOfWidgetsVisibilityDto
     *
     * @throws Exception
     */
    public function saveSettingsForDashboardWidgetsVisibility(array $arrayOfWidgetsVisibilityDto): void {

        $setting = $this->settingsLoader->getSettingsForDashboard();

        $areSettingsInDb = !empty($setting);

        if( $areSettingsInDb ){
            $settingJson = $setting->getValue();
            $dto         = SettingsDashboardDto::fromJson($settingJson);

            $dto->getWidgetSettings()->setWidgetVisibility($arrayOfWidgetsVisibilityDto);
        }else{
            $setting = new Setting();
            $dto     = SettingsDashboardService::buildDashboardSettingsDto($arrayOfWidgetsVisibilityDto);
        }

        $dashboardSettingsJson = $dto->toJson();

        $setting->setName(SettingsLoader::SETTING_NAME_DASHBOARD);
        $setting->setValue($dashboardSettingsJson);

        $this->em->persist($setting);
        $this->em->flush();
    }

    /**
     * @param SettingsCurrencyDto[] $currenciesSettingsDtos
     *
     * @throws Exception
     */
    public function saveFinancesSettingsForCurrenciesSettings(array $currenciesSettingsDtos): void {

        $setting = $this->settingsLoader->getSettingsForFinances();

        $areSettingsInDb = !empty($setting);
        if( $areSettingsInDb ){
            $settingJson = $setting->getValue();
            $dto         = SettingsFinancesDto::fromJson($settingJson);

            $dto->setSettingsCurrencyDtos($currenciesSettingsDtos);
        }else{
            $setting = new Setting();
            $dto     = SettingsFinancesService::buildFinancesSettingsDtoFromCurrenciesSettingsDtos($currenciesSettingsDtos);
        }

        $financesSettingsJson = $dto->toJson();

        $setting->setName(SettingsLoader::SETTING_NAME_FINANCES);
        $setting->setValue($financesSettingsJson);

        $this->em->persist($setting);
        $this->em->flush();
    }

    /**
     * @param ConfigDto[] $configDtos
     *
     * @throws Exception
     */
    public function saveNotificationsConfigSettings(array $configDtos): void
    {
        $settingEntity = $this->settingsLoader->getSettingsForNotifications();
        if (!empty($settingEntity)) {
            $settingJson            = $settingEntity->getValue();
            $notificationSettingDto = SettingNotificationDto::fromJson($settingJson);

            $notificationSettingDto->setConfig($configDtos);
        } else {
            $settingEntity          = new Setting();
            $notificationSettingDto = new SettingNotificationDto();

            $notificationSettingDto->setConfig($configDtos);
        }

        $financesSettingsJson = $notificationSettingDto->toJson();

        $settingEntity->setName(SettingsLoader::SETTING_NAME_NOTIFICATIONS);
        $settingEntity->setValue($financesSettingsJson);

        $this->em->persist($settingEntity);
        $this->em->flush();
    }

    /**
     * @param ConfigDto[] $moduleLockDtos
     *
     * @throws Exception
     */
    public function saveModulesLockSettings(array $moduleLockDtos): void
    {
        $settingEntity = $this->settingsLoader->getModulesSettings();
        if (!empty($settingEntity)) {
            $settingJson = $settingEntity->getValue();
            $moduleSettings = SettingsModulesDTO::fromJson($settingJson);
        } else {
            $settingEntity  = new Setting();
            $moduleSettings = new SettingsModulesDTO();
        }

        $moduleSettings->setModuleLockDtos($moduleLockDtos);

        $settingEntity->setName(SettingsLoader::SETTING_NAME_MODULES);
        $settingEntity->setValue($moduleSettings->toJson());

        $this->em->persist($settingEntity);
        $this->em->flush();
    }

}