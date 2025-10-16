<?php

namespace App\DTO\Settings\Finances;

use App\DTO\AbstractDTO;
use App\DTO\DtoInterface;
use Exception;

class SettingsFinancesDto extends AbstractDTO implements DtoInterface {

    const KEY_SETTINGS_CURRENCIES = "settingsCurrencies";

    /**
     * @var SettingsCurrencyDto[]
     */
    private $settingsCurrencyDto = [];

    /**
     * @return SettingsCurrencyDto[]
     */
    public function getSettingsCurrencyDtos(): array {
        return $this->settingsCurrencyDto;
    }

    /**
     * @param SettingsCurrencyDto[] $settingsCurrencyDto
     *
     * @throws Exception
     */
    public function setSettingsCurrencyDtos(array $settingsCurrencyDto): void {
        $hasDto = reset($settingsCurrencyDto) instanceof SettingsCurrencyDto;

        if( !$hasDto ){
            throw new \Exception("There are no SettingsCurrencyDTO in array ");
        }

        $this->settingsCurrencyDto = $settingsCurrencyDto;
    }

    /**
     * @param SettingsCurrencyDto $settingsCurrencyDto
     */
    public function addSettingsCurrencyDto(SettingsCurrencyDto $settingsCurrencyDto): void {
        $this->settingsCurrencyDto[] = $settingsCurrencyDto;
    }

    /**
     * @param string $json
     *
     * @return SettingsFinancesDto
     * @throws Exception
     */
    public static function fromJson(string $json): self{
        $financesSettingsArray = \GuzzleHttp\json_decode($json, true);
        $currencySettingJsons  = self::checkAndGetKey($financesSettingsArray, self::KEY_SETTINGS_CURRENCIES);
        $currencySettingArrays = \GuzzleHttp\json_decode($currencySettingJsons, true);

        $settingsFinancesDto = new self();

        foreach($currencySettingArrays as $currencySettingArray) {

            $currencySettingJson = \GuzzleHttp\json_encode($currencySettingArray);
            $currencySettingDto  = SettingsCurrencyDto::fromJson($currencySettingJson);
            $settingsFinancesDto->addSettingsCurrencyDto($currencySettingDto);

        }

        return $settingsFinancesDto;
    }

    /**
     * @return string
     */
    public function toJson(): string{

        $arrayOfCurrencySettingJsons = [];

        foreacH($this->getSettingsCurrencyDtos() as $currencySettings ){
            $arrayOfCurrencySettingJsons[] = $currencySettings->toArray();
        }

        $array = [
            self::KEY_SETTINGS_CURRENCIES => $arrayOfCurrencySettingJsons,
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