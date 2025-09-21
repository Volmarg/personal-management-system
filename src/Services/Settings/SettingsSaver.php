<?php

namespace App\Services\Settings;


use App\Controller\Page\SettingsDashboardController;
use App\Controller\Page\SettingsFinancesController;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDTO;
use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\DTO\Settings\Notifications\ConfigDTO;
use App\DTO\Settings\SettingNotificationDTO;
use App\DTO\Settings\SettingsDashboardDTO;
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
     * @param SettingsWidgetVisibilityDTO[] $arrayOfWidgetsVisibilityDto
     * @throws Exception
     */
    public function saveSettingsForDashboardWidgetsVisibility(array $arrayOfWidgetsVisibilityDto): void {

        $setting = $this->settingsLoader->getSettingsForDashboard();

        $areSettingsInDb = !empty($setting);

        if( $areSettingsInDb ){
            $settingJson = $setting->getValue();
            $dto         = SettingsDashboardDTO::fromJson($settingJson);

            $dto->getWidgetSettings()->setWidgetVisibility($arrayOfWidgetsVisibilityDto);
        }else{
            $setting = new Setting();
            $dto     = SettingsDashboardController::buildDashboardSettingsDto($arrayOfWidgetsVisibilityDto);
        }

        $dashboardSettingsJson = $dto->toJson();

        $setting->setName(SettingsLoader::SETTING_NAME_DASHBOARD);
        $setting->setValue($dashboardSettingsJson);

        $this->em->persist($setting);
        $this->em->flush();
    }

    /**
     * @param SettingsCurrencyDTO[] $currenciesSettingsDtos
     * @throws Exception
     */
    public function saveFinancesSettingsForCurrenciesSettings(array $currenciesSettingsDtos): void {

        $setting = $this->settingsLoader->getSettingsForFinances();

        $areSettingsInDb = !empty($setting);
        if( $areSettingsInDb ){
            $settingJson = $setting->getValue();
            $dto         = SettingsFinancesDTO::fromJson($settingJson);

            $dto->setSettingsCurrencyDtos($currenciesSettingsDtos);
        }else{
            $setting = new Setting();
            $dto     = SettingsFinancesController::buildFinancesSettingsDtoFromCurrenciesSettingsDtos($currenciesSettingsDtos);
        }

        $financesSettingsJson = $dto->toJson();

        $setting->setName(SettingsLoader::SETTING_NAME_FINANCES);
        $setting->setValue($financesSettingsJson);

        $this->em->persist($setting);
        $this->em->flush();
    }

    /**
     * @param ConfigDTO[] $configDtos
     *
     * @throws Exception
     */
    public function saveNotificationsConfigSettings(array $configDtos): void
    {
        $settingEntity = $this->settingsLoader->getSettingsForNotifications();
        if (!empty($settingEntity)) {
            $settingJson            = $settingEntity->getValue();
            $notificationSettingDto = SettingNotificationDTO::fromJson($settingJson);

            $notificationSettingDto->setConfig($configDtos);
        } else {
            $settingEntity          = new Setting();
            $notificationSettingDto = new SettingNotificationDTO();

            $notificationSettingDto->setConfig($configDtos);
        }

        $financesSettingsJson = $notificationSettingDto->toJson();

        $settingEntity->setName(SettingsLoader::SETTING_NAME_NOTIFICATIONS);
        $settingEntity->setValue($financesSettingsJson);

        $this->em->persist($settingEntity);
        $this->em->flush();
    }

}