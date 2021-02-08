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
     * @param MyContactType $entityBeforeUpdate
     * @param MyContactType $entityAfterUpdate
     * @throws Exception
     */
    public function updateContactsForUpdatedType(MyContactType $entityBeforeUpdate, MyContactType $entityAfterUpdate)
    {
        $previousContactTypeName = $entityBeforeUpdate->getName();

        $newContactTypeName      = $entityAfterUpdate->getName();
        $newContactTypeImagePath = $entityAfterUpdate->getImagePath();

        $contactsToUpdate = $this->app->repositories->myContactRepository->findContactsWithContactTypeByContactTypeName($previousContactTypeName);

        foreach($contactsToUpdate as $contactToUpdate)
        {
            $contactsTypesDtos = $contactToUpdate->getContacts()->getContactTypeDtos();

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
            $this->app->repositories->myContactRepository->saveEntity($contactToUpdate);
        }
    }

}