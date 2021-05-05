<?php

namespace App\DTO\Settings\Lock;

use App\DTO\AbstractDTO;
use App\DTO\Settings\Lock\Subsettings\SettingsModuleLockDTO;
use Exception;

/**
 * General modules settings for system
 *
 * Class SettingsModuleLockDTO
 */
class SettingsModulesDTO extends AbstractDTO
{

    const KEY_MODULE_LOCK_SETTINGS = "moduleLockSettings";

    /**
     * @var SettingsModuleLockDTO[] $moduleLockSettings
     */
    private array $moduleLockSettings = [];

    /**
     * @return SettingsModuleLockDTO[]
     */
    public function getModuleLockSettings(): array
    {
        return $this->moduleLockSettings;
    }

    /**
     * @param SettingsModuleLockDTO[] $moduleLockSettings
     */
    public function setModuleLockSettings(array $moduleLockSettings): void
    {
        $this->moduleLockSettings = $moduleLockSettings;
    }

    /**
     * @param SettingsModuleLockDTO $moduleLockDto
     */
    public function addModuleLock(SettingsModuleLockDTO $moduleLockDto): void
    {
        $this->moduleLockSettings[] = $moduleLockDto;
    }

    /**
     * @param array $dtos
     * @throws Exception
     */
    public function setModuleLockDtos(array $dtos)
    {
        $hasDto = reset($dtos) instanceof SettingsModuleLockDTO;

        if( !$hasDto ){
            throw new Exception("There are no SettingsModuleLockDTO in array ");
        }

        $this->moduleLockSettings = $dtos;
    }

    /**
     * @param string $moduleSettingsJson
     * @return SettingsModulesDTO
     * @throws Exception
     */
    public static function fromJson(string $moduleSettingsJson): self
    {
        $moduleSettingsArray      = json_decode($moduleSettingsJson, true);
        $moduleLockSettingsJson   = self::checkAndGetKey($moduleSettingsArray, self::KEY_MODULE_LOCK_SETTINGS);
        $moduleLockSettingsArrays = json_decode($moduleLockSettingsJson, true);

        $SettingsModuleDto = new self();
        foreach($moduleLockSettingsArrays as $moduleLockSettingsArray){

            $moduleLockSettingsDto = SettingsModuleLockDTO::fromArray($moduleLockSettingsArray);
            $SettingsModuleDto->addModuleLock($moduleLockSettingsDto);
        }

        return $SettingsModuleDto;
    }

    /**
     * @return string
     */
    public function toJson(): string
    {

        $arrayOfModuleLockJsons = [];
        foreacH($this->getModuleLockSettings() as $moduleLockSettings ){
            $arrayOfModuleLockJsons[] = $moduleLockSettings->toArray();
        }

        $array = [
            self::KEY_MODULE_LOCK_SETTINGS => $arrayOfModuleLockJsons,
        ];

        $json = json_encode($array);
        return $json;
    }

    public function toArray(): array {
        $json  = $this->toJson();
        $array = json_decode($json, true);

        return $array;
    }

}