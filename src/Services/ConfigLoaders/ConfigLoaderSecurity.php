<?php

namespace App\Services\ConfigLoaders;

use App\Controller\Utils\Utils;
use Exception;

class ConfigLoaderSecurity extends AbstractConfigLoader {

    /**
     * @var string $restricted_ips
     */
    private string $restricted_ips;

    /**
     * @return array
     * @throws Exception
     */
    public function getRestrictedIps(): array {
        $array_of_ips = Utils::getRealArrayForStringArray($this->restricted_ips);
        return $array_of_ips;
    }

    /**
     * Info: uses autowired param from env, but env does not support arrays, only strings
     * @param string $restricted_ips
     */
    public function setRestrictedIps(string $restricted_ips): void
    {
        $this->restricted_ips = $restricted_ips;
    }

}