<?php

namespace App\DTO\Modules\Contacts;

use App\DTO\AbstractDTO;
use App\DTO\dtoInterface;

class ContactsTypesDTO extends AbstractDTO implements dtoInterface {

    const KEY_CONTACT_TYPE_DTOS = 'contact_type_dtos';

    /**
     * @var ContactTypeDTO[]
     */
    private $contact_type_dtos = [];

    /**
     * @return ContactTypeDTO[]
     */
    public function getContactTypeDtos(): array {
        return $this->contact_type_dtos;
    }

    /**
     * @param ContactTypeDTO[] $contact_type_dtos
     */
    public function setContactTypeDtos(array $contact_type_dtos): void {
        $this->contact_type_dtos = $contact_type_dtos;
    }

    /**
     * @param ContactTypeDTO $contact_type_dto
     */
    public function addContactType(ContactTypeDTO $contact_type_dto){
        $this->contact_type_dtos[] = $contact_type_dto;
    }

    /**
     * @return array
     */
    public function toArray():array {

        $contact_types_array = [];

        foreach($this->contact_type_dtos as $contact_type_dto){
            $contact_types_array[] = $contact_type_dto->toArray();
        }

        return $contact_types_array; #info: no key - this is required for this solution
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

        $array_of_contacts_types = json_decode($json, true);
        $contacts_types_dtos     = new ContactsTypesDTO();

        foreach( $array_of_contacts_types as $contact_type ){
            $contact_type_dto = ContactTypeDTO::fromArray($contact_type);
            $contacts_types_dtos->addContactType($contact_type_dto);
        }

        return $contacts_types_dtos;
    }

}