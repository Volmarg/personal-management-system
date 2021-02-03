<?php

namespace App\Services\ConfigLoaders;

class ConfigLoaderSystem extends AbstractConfigLoader {

    /**
     * @var string $system_from_email
     */
    private string $system_from_email;

    /**
     * @return string
     */
    public function getSystemFromEmail(): string
    {
        return $this->system_from_email;
    }

    /**
     * @param string $system_from_email
     */
    public function setSystemFromEmail(string $system_from_email): void
    {
        $this->system_from_email = $system_from_email;
    }

}