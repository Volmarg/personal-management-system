<?php

namespace App\DTO\Modules\Contacts;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;

class ContactsTypesDTO extends AbstractDTO implements dtoInterface {

    const KEY_CONTACT_TYPE_DTOS = 'contact_type_dtos';

    /**
     * @var ContactTypeDTO[]
     */
    private $contactTypeDtos = [];

    /**
     * @return ContactTypeDTO[]
     */
    public function getContactTypeDtos(): array {
        return $this->contactTypeDtos;
    }

    /**
     * @param ContactTypeDTO[] $contactTypeDtos
     */
    public function setContactTypeDtos(array $contactTypeDtos): void {
        $this->contactTypeDtos = $contactTypeDtos;
    }

    /**
     * @param ContactTypeDTO $contactTypeDto
     */
    public function addContactType(ContactTypeDTO $contactTypeDto){
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
     * @return ContactsTypesDTO
     * @throws \Exception
     */
    public static function fromJson(string $json):ContactsTypesDTO {

        $arrayOfContactsTypes = json_decode($json, true);
        $contactsTypesDtos    = new ContactsTypesDTO();

        foreach( $arrayOfContactsTypes as $contactType ){
            $contactTypeDto = ContactTypeDTO::fromArray($contactType);
            $contactsTypesDtos->addContactType($contactTypeDto);
        }

        return $contactsTypesDtos;
    }

}