<?php

namespace App\Services\ConfigLoaders;

class ConfigLoaderSession extends AbstractConfigLoader {

    private $systemLockLifetime;

    /**
     * @var int $userLoginLifetime
     */
    private int $userLoginLifetime;

    /**
     * @return string
     */
    public function getSystemLockLifetime(): string {
        return $this->systemLockLifetime;
    }

    /**
     * @return int
     */
    public function getUserLoginLifetime(): int
    {
        return $this->userLoginLifetime;
    }

    public function __construct(
        string $systemLockLifetime,
        string $userLoginLifetime
    )
    {
        $this->systemLockLifetime = $systemLockLifetime;
        $this->userLoginLifetime  = $userLoginLifetime;
    }

}