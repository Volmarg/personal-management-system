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
    private $settings_currency_dto = [];

    /**
     * @return SettingsCurrencyDTO[]
     */
    public function getSettingsCurrencyDtos(): array {
        return $this->settings_currency_dto;
    }

    /**
     * @param SettingsCurrencyDTO[] $settings_currency_dto
     * @throws Exception
     */
    public function setSettingsCurrencyDtos(array $settings_currency_dto): void {
        $has_dto = reset($settings_currency_dto) instanceof SettingsCurrencyDTO;

        if( !$has_dto ){
            throw new \Exception("There are no SettingsCurrencyDTO in array ");
        }

        $this->settings_currency_dto = $settings_currency_dto;
    }

    /**
     * @param SettingsCurrencyDTO $settings_currency_dto
     */
    public function addSettingsCurrencyDto(SettingsCurrencyDTO $settings_currency_dto): void {
        $this->settings_currency_dto[] = $settings_currency_dto;
    }

    /**
     * @param string $json
     * @return SettingsFinancesDTO
     * @throws Exception
     */
    public static function fromJson(string $json): self{
        $finances_settings_array = \GuzzleHttp\json_decode($json, true);
        $currency_setting_jsons    = self::checkAndGetKey($finances_settings_array, self::KEY_SETTINGS_CURRENCIES);
        $currency_setting_arrays   = \GuzzleHttp\json_decode($currency_setting_jsons, true);

        $settings_finances_dto = new self();

        foreach($currency_setting_arrays as $currency_setting_array) {

            $currency_setting_json = \GuzzleHttp\json_encode($currency_setting_array);
            $currency_setting_dto = SettingsCurrencyDTO::fromJson($currency_setting_json);
            $settings_finances_dto->addSettingsCurrencyDto($currency_setting_dto);

        }

        return $settings_finances_dto;
    }

    /**
     * @return string
     */
    public function toJson(): string{

        $array_of_currency_setting_jsons = [];

        foreacH($this->getSettingsCurrencyDtos() as $currency_settings ){
            $array_of_currency_setting_jsons[] = $currency_settings->toArray();
        }

        $array = [
            self::KEY_SETTINGS_CURRENCIES => $array_of_currency_setting_jsons,
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