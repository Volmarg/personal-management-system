<?php

namespace App\DataFixtures\Modules\Contacts;

use App\Controller\Core\Application;
use App\Controller\Utils\Utils;
use App\DataFixtures\Providers\Modules\Contact;
use App\DataFixtures\Providers\Modules\ContactGroups;
use App\DataFixtures\Providers\Modules\ContactTypes;
use App\DTO\Modules\Contacts\ContactsTypesDTO;
use App\DTO\Modules\Contacts\ContactTypeDTO;
use App\Entity\Modules\Contacts\MyContact;
use App\Entity\Modules\Contacts\MyContactGroup;
use App\Entity\Modules\Contacts\MyContactType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Ramsey\Uuid\Uuid;

class MyContactsFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory $faker
     */
    private $faker;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->faker = Factory::create('en');
        $this->app   = $app;
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $this->addGroups($manager);
        $this->addTypes($manager);
        $this->addContacts($manager);
    }

    private function addGroups(ObjectManager $manager)
    {
        foreach(ContactGroups::ALL_CONTACT_GROUPS as $contactGroupData){

            $name  = $contactGroupData[ContactGroups::KEY_NAME];
            $icon  = $contactGroupData[ContactGroups::KEY_ICON];
            $color = $contactGroupData[ContactGroups::KEY_COLOR];


            $contactGroup = new MyContactGroup();
            $contactGroup->setName($name);
            $contactGroup->setIcon($icon);
            $contactGroup->setColor($color);
            $manager->persist($contactGroup);
        }
        $manager->flush();
    }

    private function addTypes(ObjectManager $manager)
    {
        foreach(ContactTypes::ALL_CONTACT_TYPES as $contactTypeData){

            $name      = $contactTypeData[ContactTypes::KEY_NAME];
            $imagePath = $contactTypeData[ContactTypes::KEY_IMAGE_PATH];

            $contactType = new MyContactType();
            $contactType->setName($name);
            $contactType->setImagePath($imagePath);
            $manager->persist($contactType);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function addContacts(ObjectManager $manager) {

        for( $x = 0; $x <= 22 ; $x++) {

            $personName     = $this->faker->firstName . ' ' . $this->faker->lastName;
            $personNickname = $this->faker->realText(50);
            $personPicture  = $this->getUserPictureUrl();
            $personGroup    = $this->getGroup();


            $contactTypeLocationCity  = $this->faker->city;
            $contactTypeLocationName  = ContactTypes::CONTACT_TYPE_LOCATION[ContactTypes::KEY_NAME];
            $contactTypeLocationImage = ContactTypes::CONTACT_TYPE_LOCATION[ContactTypes::KEY_IMAGE_PATH];

            $contactTypeLocation = new ContactTypeDTO();
            $contactTypeLocation->setUuid(Uuid::uuid1());
            $contactTypeLocation->setName($contactTypeLocationName);
            $contactTypeLocation->setIconPath($contactTypeLocationImage);
            $contactTypeLocation->setDetails($contactTypeLocationCity);



            $contactTypeEmailAddress = $this->faker->email;
            $contactTypeEmailName    = ContactTypes::CONTACT_TYPE_EMAIL[ContactTypes::KEY_NAME];
            $contactTypeEmailImage   = ContactTypes::CONTACT_TYPE_EMAIL[ContactTypes::KEY_IMAGE_PATH];

            $contactTypeEmail = new ContactTypeDTO();
            $contactTypeEmail->setUuid(Uuid::uuid1());
            $contactTypeEmail->setName($contactTypeEmailName);
            $contactTypeEmail->setIconPath($contactTypeEmailImage);
            $contactTypeEmail->setDetails($contactTypeEmailAddress);


            $contactTypeMobileNumber = $this->faker->email;
            $contactTypeMobileName   = ContactTypes::CONTACT_TYPE_MOBILE[ContactTypes::KEY_NAME];
            $contactTypeMobileImage  = ContactTypes::CONTACT_TYPE_MOBILE[ContactTypes::KEY_IMAGE_PATH];

            $contactTypeMobile = new ContactTypeDTO();
            $contactTypeMobile->setUuid(Uuid::uuid1());
            $contactTypeMobile->setName($contactTypeMobileName);
            $contactTypeMobile->setIconPath($contactTypeMobileImage);
            $contactTypeMobile->setDetails($contactTypeMobileNumber);

            $additionalContactTypeData  = Utils::arrayGetRandom(ContactTypes::ADDITIONAL_CONTACT_TYPES_EXAMPLES);
            $additionalContactTypeName  = $additionalContactTypeData[ContactTypes::KEY_NAME];
            $additionalContactTypeImage = $additionalContactTypeData[ContactTypes::KEY_IMAGE_PATH];
            $additionalContactTypeNick  = $this->faker->userName;

            $contactTypeAdditional = new ContactTypeDTO();
            $contactTypeAdditional->setUuid(Uuid::uuid1());
            $contactTypeAdditional->setName($additionalContactTypeName);
            $contactTypeAdditional->setIconPath($additionalContactTypeImage);
            $contactTypeAdditional->setDetails($additionalContactTypeNick);

            $contactTypeDtos = [
                $contactTypeEmail,
                $contactTypeMobile,
                $contactTypeLocation,
                $contactTypeAdditional,
            ];

            $contactsTypesDtos = new ContactsTypesDTO();
            $contactsTypesDtos->setContactTypeDtos($contactTypeDtos);
            $contactsTypesJson = $contactsTypesDtos->toJson();

            $contact = new MyContact();
            $contact->setName($personName);
            $contact->setContacts($contactsTypesJson);
            $contact->setDescription($personNickname);

            $colorSet = Utils::arrayGetRandom(Contact::ALL_COLORS_SETS);
            $descriptionBackgroundColor = $colorSet[Contact::KEY_DESCRIPTION_BACKGROUND_COLOR];
            $nameBackgroundColor        = $colorSet[Contact::KEY_NAME_BACKGROUND_COLOR];

            $contact->setImagePath($personPicture);
            $contact->setGroup($personGroup);
            $contact->setDescriptionBackgroundColor($descriptionBackgroundColor);
            $contact->setNameBackgroundColor($nameBackgroundColor);

            $manager->persist($contact);
        }

        $manager->flush();
    }

    private function getUserPictureUrl()
    {
        $num = rand(1, 35);
        $url = "https://randomuser.me/api/portraits/men/{$num}.jpg" ;
        return $url;
    }

    private function getGroup()
    {
        $allGroups = $this->app->repositories->myContactGroupRepository->findAll();
        $group      = Utils::arrayGetRandom($allGroups);
        return $group;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 14;
    }

}
