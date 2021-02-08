<?php

namespace App\Controller\Page;

use App\Services\Settings\SettingsLoader;
use App\Services\Core\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * This class contains only the views rendering logic for settings
 * Class SettingsViewController
 * @package App\Controller\Page
 */
class SettingsViewController extends AbstractController {

    /**
     * @var \App\Services\Core\Translator
     */
    private $translator;

    /**
     * @var SettingsLoader $settingsLoader
     */
    private $settingsLoader;

    /**
     * SettingsController constructor.
     * @param Translator $translator
     * @param SettingsLoader $settingsLoader
     */
    public function __construct(Translator $translator, SettingsLoader $settingsLoader) {
        $this->settingsLoader = $settingsLoader;
        $this->translator     = $translator;
    }

}
