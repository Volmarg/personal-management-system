<?php

namespace App\Controller\Page;

use App\Services\Settings\SettingsLoader;
use App\Services\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * This class contains only the views rendering logic for settings
 * Class SettingsViewController
 * @package App\Controller\Page
 */
class SettingsViewController extends AbstractController {

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var SettingsLoader $settings_loader
     */
    private $settings_loader;

    /**
     * SettingsController constructor.
     * @param Translator $translator
     * @param SettingsLoader $settings_loader
     */
    public function __construct(Translator $translator, SettingsLoader $settings_loader) {
        $this->settings_loader = $settings_loader;
        $this->translator      = $translator;
    }

}
