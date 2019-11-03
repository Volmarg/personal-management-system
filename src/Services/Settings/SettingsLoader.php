<?php

namespace App\Services\Settings;

use App\Controller\Utils\Application;
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
     * @throws DBALException
     */
    public function fetchSettingsForDashboard(): string{
        $setting_json = $this->app->repositories->settingRepository->fetchSettingsForDashboard();
        return $setting_json;
    }
}