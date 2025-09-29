<?php

namespace App\Controller\Page;

use App\Services\Settings\SettingsLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * This class contains only the views rendering logic for settings
 * Class SettingsViewController
 * @package App\Controller\Page
 */
class SettingsViewController extends AbstractController {

    /**
     * @var SettingsLoader $settingsLoader
     */
    private $settingsLoader;

    /**
     * SettingsController constructor.
     * @param SettingsLoader $settingsLoader
     */
    public function __construct(SettingsLoader $settingsLoader) {
        $this->settingsLoader = $settingsLoader;
    }

}
