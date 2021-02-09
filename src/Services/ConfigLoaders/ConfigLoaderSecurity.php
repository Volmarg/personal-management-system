<?php

namespace App\Services\ConfigLoaders;

use App\Controller\Utils\Utils;
use Exception;

class ConfigLoaderSecurity extends AbstractConfigLoader {

    /**
     * @var string $restrictedIps
     */
    private string $restrictedIps;

    /**
     * @return array
     * @throws Exception
     */
    public function getRestrictedIps(): array {
        $arrayOfIps = Utils::getRealArrayForStringArray($this->restrictedIps);
        return $arrayOfIps;
    }

    /**
     * Info: uses autowired param from env, but env does not support arrays, only strings
     * @param string $restrictedIps
     */
    public function setRestrictedIps(string $restrictedIps): void
    {
        $this->restrictedIps = $restrictedIps;
    }

}