<?php

namespace App\DTO\Settings;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;
use App\DTO\Settings\Notifications\ConfigDTO;
use Exception;

class SettingNotificationDTO extends AbstractDTO implements dtoInterface
{

    const KEY_CONFIG = 'config';

    /**
     * @var ConfigDTO[]
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
     * @return SettingNotificationDTO
     * @throws Exception
     */
    public static function fromJson(string $configArray): self
    {
        $dataArray    = json_decode($configArray, true);
        $configJsons  = self::checkAndGetKey($dataArray, self::KEY_CONFIG);
        $configArrays = json_decode($configJsons, true);

        $configs = [];
        foreach ($configArrays as $configArray) {
            $configs[] = ConfigDTO::fromJson(json_encode($configArray));
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
        $configsArray = array_map(fn(ConfigDTO $config) => $config->toArray(), $this->getConfig());
        return json_encode([
            self::KEY_CONFIG => $configsArray,
        ]);
    }

}