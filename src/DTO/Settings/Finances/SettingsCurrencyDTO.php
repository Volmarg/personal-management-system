<?php

namespace App\DTO\Settings\Finances;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;
use Exception;

class SettingsCurrencyDTO extends AbstractDTO implements dtoInterface {

    const KEY_NAME       = 'name';
    const KEY_SYMBOL     = 'symbol';
    const KEY_IS_DEFAULT = "is_default";
    const KEY_MULTIPLIER = "multiplier";

    /**
     * @var string $name
     */
    private $name = "name";

    /**
     * @var string $symbol
     */
    private $symbol = "";

    /**
     * @var bool $isDefault
     */
    private $isDefault = false;

    /**
     * @var int $multiplier
     */
    private $multiplier = 1;

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSymbol(): string {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     */
    public function setSymbol(string $symbol): void {
        $this->symbol = $symbol;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     */
    public function setIsDefault(bool $isDefault): void {
        $this->isDefault = $isDefault;
    }

    /**
     * @return int
     */
    public function getMultiplier(): int {
        return $this->multiplier;
    }

    /**
     * @param int $multiplier
     */
    public function setMultiplier(int $multiplier): void {
        $this->multiplier = $multiplier;
    }

    /**
     * @param string $json
     * @return SettingsCurrencyDTO
     * @throws Exception
     */
    public static function fromJson(string $json): self {
        $array = \GuzzleHttp\json_decode($json, true);

        $name       = self::checkAndGetKey($array, self::KEY_NAME);
        $symbol     = self::checkAndGetKey($array, self::KEY_SYMBOL);
        $is_default = self::checkAndGetKey($array, self::KEY_IS_DEFAULT);
        $multiplier = self::checkAndGetKey($array, self::KEY_MULTIPLIER);

        $dto = new self();
        $dto->setName($name);
        $dto->setIsDefault($is_default);
        $dto->setMultiplier($multiplier);
        $dto->setSymbol($symbol);

        return $dto;
    }

    /**
     * @return string
     */
    public function toJson(): string{

        $array = $this->toArray();
        $json  = json_encode($array);

        return $json;
    }

    public function toArray(): array {

        $array = [
            self::KEY_NAME       => $this->getName(),
            self::KEY_SYMBOL     => $this->getSymbol(),
            self::KEY_IS_DEFAULT => $this->isDefault(),
            self::KEY_MULTIPLIER => $this->getMultiplier(),
        ];

        return $array;
    }

}