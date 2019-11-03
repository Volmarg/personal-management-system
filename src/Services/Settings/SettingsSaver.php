<?php

namespace App\Services\Settings;

use App\Controller\Utils\Application;
use App\DTO\Settings\SettingsDashboardDTO;
use App\Entity\Setting;

/**
 * Class SettingsSaver
 * @package App\Services\Files
 */
class SettingsSaver {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * DatabaseExporter constructor.
     * @param Application $app
     * @throws \Exception
     */
    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function saveSettingsForDashboard(SettingsDashboardDTO $dto){
        $json = $dto->toJson();

        $setting = new Setting();
        $setting->setName(SettingsLoader::SETTING_NAME_DASHBOARD);
        $setting->setValue($json);

        $this->app->em->persist($setting);
        $this->app->em->flush();
    }

}