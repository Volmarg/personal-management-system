<?php

namespace App\Services\ConfigLoaders;

class ConfigLoaderSession extends AbstractConfigLoader {

    private $system_lock_lifetime;

    /**
     * @var int $user_login_lifetime
     */
    private int $user_login_lifetime;

    /**
     * @return string
     */
    public function getSystemLockLifetime(): string {
        return $this->system_lock_lifetime;
    }

    /**
     * @return int
     */
    public function getUserLoginLifetime(): int
    {
        return $this->user_login_lifetime;
    }

    public function __construct(
        string $system_lock_lifetime,
        string $user_login_lifetime
    )
    {
        $this->system_lock_lifetime = $system_lock_lifetime;
        $this->user_login_lifetime  = $user_login_lifetime;
    }

}