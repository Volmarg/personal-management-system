<?php

namespace App\Controller\Modules\Contacts;

use App\Controller\Core\Application;
use App\DTO\Modules\Contacts\ContactsTypesDTO;
use App\Entity\Modules\Contacts\MyContactType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyContactsSettingsController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @param MyContactType $entity_before_update
     * @param MyContactType $entity_after_update
     * @throws Exception
     */
    public function updateContactsForUpdatedType(MyContactType $entity_before_update, MyContactType $entity_after_update)
    {
        $previous_contact_type_name = $entity_before_update->getName();

        $new_contact_type_name       = $entity_after_update->getName();
        $new_contact_type_image_path = $entity_after_update->getImagePath();

        $contacts_to_update = $this->app->repositories->myContactRepository->findContactsWithContactTypeByContactTypeName($previous_contact_type_name);

        foreach($contacts_to_update as $contact_to_update)
        {
            $contacts_types_dtos = $contact_to_update->getContacts()->getContactTypeDtos();

            foreach($contacts_types_dtos as $index => $contact_type_dto){
                if( strtolower($contact_type_dto->getName()) === strtolower($previous_contact_type_name) )
                {
                    $contact_type_dto->setName($new_contact_type_name);
                    $contact_type_dto->setIconPath($new_contact_type_image_path);
                    $contacts_types_dtos[$index] = $contact_type_dto;
                }
            }

            $contacts_types_dto = new ContactsTypesDTO();
            $contacts_types_dto->setContactTypeDtos($contacts_types_dtos);

            $json = $contacts_types_dto->toJson();

            $contact_to_update->setContacts($json);
            $this->app->repositories->myContactRepository->saveEntity($contact_to_update);
        }
    }

}