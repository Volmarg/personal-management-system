<?php

namespace App\Controller\Modules\Contacts;

use App\DTO\Modules\Contacts\ContactsTypesDTO;
use App\Entity\Modules\Contacts\MyContactType;
use App\Repository\Modules\Contacts\MyContactRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyContactsSettingsController extends AbstractController {

    public function __construct(
        private readonly MyContactRepository $contactRepository
    ) {
    }

    /**
     * @param MyContactType $entityBeforeUpdate
     * @param MyContactType $entityAfterUpdate
     * @throws Exception
     */
    public function updateContactsForUpdatedType(MyContactType $entityBeforeUpdate, MyContactType $entityAfterUpdate)
    {
        $previousContactTypeName = $entityBeforeUpdate->getName();

        $newContactTypeName      = $entityAfterUpdate->getName();
        $newContactTypeImagePath = $entityAfterUpdate->getImagePath();

        $contactsToUpdate = $this->contactRepository->findContactsWithContactTypeByContactTypeName($previousContactTypeName);

        foreach($contactsToUpdate as $contactToUpdate)
        {
            $contactsTypesDtos = $contactToUpdate->getContactTypesDto()->getContactTypeDtos();

            foreach($contactsTypesDtos as $index => $contactTypeDto){
                if( strtolower($contactTypeDto->getName()) === strtolower($previousContactTypeName) )
                {
                    $contactTypeDto->setName($newContactTypeName);
                    $contactTypeDto->setIconPath($newContactTypeImagePath);
                    $contactsTypesDtos[$index] = $contactTypeDto;
                }
            }

            $contactsTypesDto = new ContactsTypesDTO();
            $contactsTypesDto->setContactTypeDtos($contactsTypesDtos);

            $json = $contactsTypesDto->toJson();

            $contactToUpdate->setContacts($json);
            $this->contactRepository->saveEntity($contactToUpdate);
        }
    }

}