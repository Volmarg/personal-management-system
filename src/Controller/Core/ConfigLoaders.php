<?php


namespace App\Controller\Core;


use App\Services\ConfigLoaders\ConfigLoaderSecurity;
use App\Services\ConfigLoaders\ConfigLoaderSystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConfigLoaders extends AbstractController {

    /**
     * @var ConfigLoaderSecurity $configLoaderSecurity
     */
    private ConfigLoaderSecurity $configLoaderSecurity;

    /**
     * @var ConfigLoaderSystem $configLoaderSystem
     */
    private ConfigLoaderSystem $configLoaderSystem;

    /**
     * @return ConfigLoaderSecurity
     */
    public function getConfigLoaderSecurity(): ConfigLoaderSecurity
    {
        return $this->configLoaderSecurity;
    }

    /**
     * @return ConfigLoaderSystem
     */
    public function getConfigLoaderSystem(): ConfigLoaderSystem
    {
        return $this->configLoaderSystem;
    }

    public function __construct(
        ConfigLoaderSecurity $configLoaderSecurity,
        ConfigLoaderSystem   $configLoaderSystem
    )
    {
        $this->configLoaderSecurity = $configLoaderSecurity;
        $this->configLoaderSystem   = $configLoaderSystem;
    }

}