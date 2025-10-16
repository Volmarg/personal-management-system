<?php

namespace App\DTO\Settings;

use App\DTO\AbstractDTO;
use App\DTO\DtoInterface;
use App\DTO\Settings\Notifications\ConfigDto;
use Exception;

class SettingNotificationDto extends AbstractDTO implements DtoInterface
{

    const KEY_CONFIG = 'config';

    /**
     * @var ConfigDto[]
     */
    private array $config = [];

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @param string $configArray
     *
     * @return SettingNotificationDto
     * @throws Exception
     */
    public static function fromJson(string $configArray): self
    {
        $dataArray    = json_decode($configArray, true);
        $configJsons  = self::checkAndGetKey($dataArray, self::KEY_CONFIG);
        $configArrays = json_decode($configJsons, true);

        $configs = [];
        foreach ($configArrays as $configArray) {
            $configs[] = ConfigDto::fromJson(json_encode($configArray));
        }

        $settingsDashboardDto = new self();
        $settingsDashboardDto->setConfig($configs);

        return $settingsDashboardDto;
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        $configsArray = array_map(fn(ConfigDto $config) => $config->toArray(), $this->getConfig());
        return json_encode([
            self::KEY_CONFIG => $configsArray,
        ]);
    }

}