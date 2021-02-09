<?php

namespace App\Services\ConfigLoaders;

class ConfigLoaderSystem extends AbstractConfigLoader {

    /**
     * @var string $systemFromEmail
     */
    private string $systemFromEmail;

    /**
     * @return string
     */
    public function getSystemFromEmail(): string
    {
        return $this->systemFromEmail;
    }

    /**
     * @param string $systemFromEmail
     */
    public function setSystemFromEmail(string $systemFromEmail): void
    {
        $this->systemFromEmail = $systemFromEmail;
    }

}