<?php

namespace App\DTO\Modules\Contacts;

use App\DTO\AbstractDTO;
use App\DTO\DtoInterface;

class ContactsTypesDto extends AbstractDTO implements DtoInterface {

    /**
     * @var ContactTypeDto[]
     */
    private $contactTypeDtos = [];

    /**
     * @return ContactTypeDto[]
     */
    public function getContactTypeDtos(): array {
        return $this->contactTypeDtos;
    }

    /**
     * @param ContactTypeDto[] $contactTypeDtos
     */
    public function setContactTypeDtos(array $contactTypeDtos): void {
        $this->contactTypeDtos = $contactTypeDtos;
    }

    /**
     * @param ContactTypeDto $contactTypeDto
     */
    public function addContactType(ContactTypeDto $contactTypeDto){
        $this->contactTypeDtos[] = $contactTypeDto;
    }

    /**
     * @return array
     */
    public function toArray():array {

        $contactTypesArray = [];

        foreach($this->contactTypeDtos as $contactTypeDto){
            $contactTypesArray[] = $contactTypeDto->toArray();
        }

        return $contactTypesArray; #info: no key - this is required for this solution
    }

    /**
     * @return string
     */
    public function toJson():string {
        $array  = $this->toArray();
        $json   = json_encode($array);
        return $json;
    }

    /**
     * @param string $json
     *
     * @return ContactsTypesDto
     * @throws \Exception
     */
    public static function fromJson(string $json): ContactsTypesDto {
        if (empty($json)) {
            return new ContactsTypesDto();
        }

        $arrayOfContactsTypes = json_decode($json, true);
        $contactsTypesDtos    = new ContactsTypesDto();

        foreach( $arrayOfContactsTypes as $contactType ){
            $contactTypeDto = ContactTypeDto::fromArray($contactType);
            $contactsTypesDtos->addContactType($contactTypeDto);
        }

        return $contactsTypesDtos;
    }

}