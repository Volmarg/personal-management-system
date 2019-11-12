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
     * @var string $icon_path
     */
    private $icon_path;

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
        return $this->icon_path;
    }

    /**
     * @param string $icon_path
     */
    public function setIconPath(string $icon_path): void {
        $this->icon_path = $icon_path;
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

        $name       = static::checkAndGetKey($array, self::KEY_NAME);
        $icon_path  = static::checkAndGetKey($array, self::KEY_ICON_PATH);
        $details    = static::checkAndGetKey($array, self::KEY_DETAILS);
        $uuid       = static::checkAndGetKey($array, self::KEY_UUID);

        $contact_type_dto = new ContactTypeDTO();
        $contact_type_dto->setName($name);
        $contact_type_dto->setIconPath($icon_path);
        $contact_type_dto->setDetails($details);
        $contact_type_dto->setUuid($uuid);

        return $contact_type_dto;
    }

    /**
     * @param string $json
     * @return ContactTypeDTO
     */
    public static function fromJson(string $json):ContactTypeDTO {
        $array              = json_decode($json, true);
        $contact_type_dto   = self::fromJson($array);
        return $contact_type_dto;
    }


}