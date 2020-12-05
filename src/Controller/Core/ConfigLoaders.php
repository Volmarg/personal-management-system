<?php


namespace App\Controller\Core;


use App\Services\ConfigLoaders\ConfigLoaderSecurity;
use App\Services\ConfigLoaders\ConfigLoaderSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConfigLoaders extends AbstractController {

    /**
     * @var ConfigLoaderSession $configLoaderSession
     */
    private $configLoaderSession;

    /**
     * @var ConfigLoaderSecurity $config_loader_security
     */
    private ConfigLoaderSecurity $config_loader_security;

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
        return $this->config_loader_security;
    }

    public function __construct(
        ConfigLoaderSession  $config_loader_session,
        ConfigLoaderSecurity $config_loader_security
    )
    {
        $this->configLoaderSession    = $config_loader_session;
        $this->config_loader_security = $config_loader_security;
    }

}