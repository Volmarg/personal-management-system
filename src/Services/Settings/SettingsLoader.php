<?php

namespace App\Services\Settings;

use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
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
     * @var Repositories $repositories
     */
    private $repositories;

    /**
     * DatabaseExporter constructor.
     * @param Repositories $repositories
     * @throws \Exception
     */
    public function __construct(Repositories $repositories) {
        $this->repositories = $repositories;
    }

    /**
     * @return Setting|null
     */
    public function getSettingsForDashboard(): ?Setting {
        $setting = $this->repositories->settingRepository->getSettingsForDashboard();
        return $setting;
    }
}