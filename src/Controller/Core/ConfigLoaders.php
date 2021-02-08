<?php


namespace App\Controller\Core;


use App\Services\ConfigLoaders\ConfigLoaderSecurity;
use App\Services\ConfigLoaders\ConfigLoaderSession;
use App\Services\ConfigLoaders\ConfigLoaderSystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConfigLoaders extends AbstractController {

    /**
     * @var ConfigLoaderSession $configLoaderSession
     */
    private ConfigLoaderSession $configLoaderSession;

    /**
     * @var ConfigLoaderSecurity $configLoaderSecurity
     */
    private ConfigLoaderSecurity $configLoaderSecurity;

    /**
     * @var ConfigLoaderSystem $configLoaderSystem
     */
    private ConfigLoaderSystem $configLoaderSystem;

    /**
     * @return ConfigLoaderSession
     */
    public function getConfigLoaderSession(): ConfigLoaderSession {
        return $this->configLoaderSession;
    }

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
        ConfigLoaderSession  $configLoaderSession,
        ConfigLoaderSecurity $configLoaderSecurity,
        ConfigLoaderSystem   $configLoaderSystem
    )
    {
        $this->configLoaderSession  = $configLoaderSession;
        $this->configLoaderSecurity = $configLoaderSecurity;
        $this->configLoaderSystem   = $configLoaderSystem;
    }

}