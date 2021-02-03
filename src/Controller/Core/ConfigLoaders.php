<?php


namespace App\Controller\Core;


use App\Services\ConfigLoaders\ConfigLoaderSecurity;
use App\Services\ConfigLoaders\ConfigLoaderSession;
use App\Services\ConfigLoaders\ConfigLoaderSystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConfigLoaders extends AbstractController {

    /**
     * @var ConfigLoaderSession $config_loader_session
     */
    private ConfigLoaderSession $config_loader_session;

    /**
     * @var ConfigLoaderSecurity $config_loader_security
     */
    private ConfigLoaderSecurity $config_loader_security;

    /**
     * @var ConfigLoaderSystem $config_loader_system
     */
    private ConfigLoaderSystem $config_loader_system;

    /**
     * @return ConfigLoaderSession
     */
    public function getConfigLoaderSession(): ConfigLoaderSession {
        return $this->config_loader_session;
    }

    /**
     * @return ConfigLoaderSecurity
     */
    public function getConfigLoaderSecurity(): ConfigLoaderSecurity
    {
        return $this->config_loader_security;
    }

    /**
     * @return ConfigLoaderSystem
     */
    public function getConfigLoaderSystem(): ConfigLoaderSystem
    {
        return $this->config_loader_system;
    }

    public function __construct(
        ConfigLoaderSession  $config_loader_session,
        ConfigLoaderSecurity $config_loader_security,
        ConfigLoaderSystem   $config_loader_system
    )
    {
        $this->config_loader_session  = $config_loader_session;
        $this->config_loader_security = $config_loader_security;
        $this->config_loader_system   = $config_loader_system;
    }

}