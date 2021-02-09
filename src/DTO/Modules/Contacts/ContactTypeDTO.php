<?php

namespace App\DTO\Modules\Contacts;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;

class ContactTypeDTO extends AbstractDTO implements dtoInterface {

    const KEY_NAME      = 'name';
    const KEY_ICON_PATH = 'icon_path';
    const KEY_DETAILS   = 'details';
    const KEY_UUID      = 'uuid';

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $iconPath
     */
    private $iconPath;

    /**
     * @var string $details
     */
    private $details;

    /**
     * This value is later used on front to identify which contact type in json we change as one contact may have few discord type contacts
     * @var string $uuid
     */
    private $uuid;

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
    public function getIconPath(): string {
        return $this->iconPath;
    }

    /**
     * @param string $iconPath
     */
    public function setIconPath(string $iconPath): void {
        $this->iconPath = $iconPath;
    }

    /**
     * @return string
     */
    public function getDetails(): string {
        return $this->details;
    }

    /**
     * @param string $details
     */
    public function setDetails(string $details): void {
        $this->details = $details;
    }

    /**
     * @return string
     */
    public function getUuid(): string {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid(string $uuid): void {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function toJson():string {
        $array = $this->toArray();
        $json  = json_decode($array);
        return $json;
    }

    public function toArray():array {
        return [
            self::KEY_NAME      => $this->getName(),
            self::KEY_UUID      => $this->getUuid(),
            self::KEY_DETAILS   => $this->getDetails(),
            self::KEY_ICON_PATH => $this->getIconPath(),
        ];
    }

    /**
     * @param array $array
     * @return ContactTypeDTO
     * @throws \Exception
     */
    public static function fromArray(array $array): ContactTypeDTO {

        $name     = static::checkAndGetKey($array, self::KEY_NAME);
        $iconPath = static::checkAndGetKey($array, self::KEY_ICON_PATH);
        $details  = static::checkAndGetKey($array, self::KEY_DETAILS);
        $uuid     = static::checkAndGetKey($array, self::KEY_UUID);

        $contactTypeDto = new ContactTypeDTO();
        $contactTypeDto->setName($name);
        $contactTypeDto->setIconPath($iconPath);
        $contactTypeDto->setDetails($details);
        $contactTypeDto->setUuid($uuid);

        return $contactTypeDto;
    }

    /**
     * @param string $json
     * @return ContactTypeDTO
     */
    public static function fromJson(string $json):ContactTypeDTO {
        $array          = json_decode($json, true);
        $contactTypeDto = self::fromJson($array);
        return $contactTypeDto;
    }


}