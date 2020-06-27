<?php

namespace App\Services\ConfigLoaders;

class ConfigLoaderSession extends AbstractConfigLoader {

    private $system_lock_lifetime;

    /**
     * @return string
     */
    public function getSystemLockLifetime(): string {
        return $this->system_lock_lifetime;
    }

    public function __construct(string $system_lock_lifetime)
    {
        $this->system_lock_lifetime = $system_lock_lifetime;
    }

}