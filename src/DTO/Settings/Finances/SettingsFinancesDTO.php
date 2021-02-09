<?php

namespace App\DTO\Settings\Finances;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;
use Exception;

class SettingsFinancesDTO extends AbstractDTO implements dtoInterface {

    const KEY_SETTINGS_CURRENCIES = "settingsCurrencies";

    /**
     * @var SettingsCurrencyDTO[]
     */
    private $settingsCurrencyDto = [];

    /**
     * @return SettingsCurrencyDTO[]
     */
    public function getSettingsCurrencyDtos(): array {
        return $this->settingsCurrencyDto;
    }

    /**
     * @param SettingsCurrencyDTO[] $settingsCurrencyDto
     * @throws Exception
     */
    public function setSettingsCurrencyDtos(array $settingsCurrencyDto): void {
        $hasDto = reset($settingsCurrencyDto) instanceof SettingsCurrencyDTO;

        if( !$hasDto ){
            throw new \Exception("There are no SettingsCurrencyDTO in array ");
        }

        $this->settingsCurrencyDto = $settingsCurrencyDto;
    }

    /**
     * @param SettingsCurrencyDTO $settingsCurrencyDto
     */
    public function addSettingsCurrencyDto(SettingsCurrencyDTO $settingsCurrencyDto): void {
        $this->settingsCurrencyDto[] = $settingsCurrencyDto;
    }

    /**
     * @param string $json
     * @return SettingsFinancesDTO
     * @throws Exception
     */
    public static function fromJson(string $json): self{
        $financesSettingsArray = \GuzzleHttp\json_decode($json, true);
        $currencySettingJsons  = self::checkAndGetKey($financesSettingsArray, self::KEY_SETTINGS_CURRENCIES);
        $currencySettingArrays = \GuzzleHttp\json_decode($currencySettingJsons, true);

        $settingsFinancesDto = new self();

        foreach($currencySettingArrays as $currencySettingArray) {

            $currencySettingJson = \GuzzleHttp\json_encode($currencySettingArray);
            $currencySettingDto = SettingsCurrencyDTO::fromJson($currencySettingJson);
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