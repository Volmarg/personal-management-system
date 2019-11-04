<?php

namespace App\Services\Settings;

use App\Controller\Utils\Application;
use App\Entity\Setting;
use Doctrine\DBAL\DBALException;

/**
 * This class is responsible for fetching settings json from DB
 * Class SettingsLoader
 * @package App\Services\Files
 */
class SettingsLoader {

    const SETTING_NAME_DASHBOARD = 'dashboard';

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

    /**
     * @return Setting|null
     */
    public function getSettingsForDashboard(): ?Setting {
        $setting = $this->app->repositories->settingRepository->getSettingsForDashboard();
        return $setting;
    }
}