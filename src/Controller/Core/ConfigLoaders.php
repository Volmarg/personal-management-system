<?php


namespace App\Controller\Core;


use App\Services\ConfigLoaders\ConfigLoaderSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConfigLoaders extends AbstractController {

    /**
     * @var ConfigLoaderSession $configLoaderSession
     */
    private $configLoaderSession;

    /**
     * @return ConfigLoaderSession
     */
    public function getConfigLoaderSession(): ConfigLoaderSession {
        return $this->configLoaderSession;
    }

    public function __construct(ConfigLoaderSession $config_loader_session)
    {
        $this->configLoaderSession = $config_loader_session;
    }

}