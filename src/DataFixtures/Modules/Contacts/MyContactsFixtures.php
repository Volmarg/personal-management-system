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
use Doctrine\Common\Persistence\ObjectManager;
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
        foreach(ContactGroups::ALL_CONTACT_GROUPS as $contact_group_data){

            $name  = $contact_group_data[ContactGroups::KEY_NAME];
            $icon  = $contact_group_data[ContactGroups::KEY_ICON];
            $color = $contact_group_data[ContactGroups::KEY_COLOR];


            $contact_group = new MyContactGroup();
            $contact_group->setName($name);
            $contact_group->setIcon($icon);
            $contact_group->setColor($color);
            $manager->persist($contact_group);
        }
        $manager->flush();
    }

    private function addTypes(ObjectManager $manager)
    {
        foreach(ContactTypes::ALL_CONTACT_TYPES as $contact_type_data){

            $name       = $contact_type_data[ContactTypes::KEY_NAME];
            $image_path = $contact_type_data[ContactTypes::KEY_IMAGE_PATH];

            $contact_type = new MyContactType();
            $contact_type->setName($name);
            $contact_type->setImagePath($image_path);
            $manager->persist($contact_type);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function addContacts(ObjectManager $manager) {

        for( $x = 0; $x <= 22 ; $x++) {

            $person_name     = $this->faker->firstName . ' ' . $this->faker->lastName;
            $person_nickname = $this->faker->realText(50);
            $person_picture  = $this->getUserPictureUrl();
            $person_group    = $this->getGroup();


            $contact_type_location_city  = $this->faker->city;
            $contact_type_location_name  = ContactTypes::CONTACT_TYPE_LOCATION[ContactTypes::KEY_NAME];
            $contact_type_location_image = ContactTypes::CONTACT_TYPE_LOCATION[ContactTypes::KEY_IMAGE_PATH];

            $contact_type_location = new ContactTypeDTO();
            $contact_type_location->setUuid(Uuid::uuid1());
            $contact_type_location->setName($contact_type_location_name);
            $contact_type_location->setIconPath($contact_type_location_image);
            $contact_type_location->setDetails($contact_type_location_city);



            $contact_type_email_address  = $this->faker->email;
            $contact_type_email_name     = ContactTypes::CONTACT_TYPE_EMAIL[ContactTypes::KEY_NAME];
            $contact_type_email_image    = ContactTypes::CONTACT_TYPE_EMAIL[ContactTypes::KEY_IMAGE_PATH];

            $contact_type_email = new ContactTypeDTO();
            $contact_type_email->setUuid(Uuid::uuid1());
            $contact_type_email->setName($contact_type_email_name);
            $contact_type_email->setIconPath($contact_type_email_image);
            $contact_type_email->setDetails($contact_type_email_address);


            $contact_type_mobile_number  = $this->faker->email;
            $contact_type_mobile_name     = ContactTypes::CONTACT_TYPE_MOBILE[ContactTypes::KEY_NAME];
            $contact_type_mobile_image    = ContactTypes::CONTACT_TYPE_MOBILE[ContactTypes::KEY_IMAGE_PATH];

            $contact_type_mobile = new ContactTypeDTO();
            $contact_type_mobile->setUuid(Uuid::uuid1());
            $contact_type_mobile->setName($contact_type_mobile_name);
            $contact_type_mobile->setIconPath($contact_type_mobile_image);
            $contact_type_mobile->setDetails($contact_type_mobile_number);

            $additional_contact_type_data  = Utils::arrayGetRandom(ContactTypes::ADDITIONAL_CONTACT_TYPES_EXAMPLES);
            $additional_contact_type_name  = $additional_contact_type_data[ContactTypes::KEY_NAME];
            $additional_contact_type_image = $additional_contact_type_data[ContactTypes::KEY_IMAGE_PATH];
            $additional_contact_type_nick  = $this->faker->userName;

            $contact_type_additional = new ContactTypeDTO();
            $contact_type_additional->setUuid(Uuid::uuid1());
            $contact_type_additional->setName($additional_contact_type_name);
            $contact_type_additional->setIconPath($additional_contact_type_image);
            $contact_type_additional->setDetails($additional_contact_type_nick);

            $contact_type_dtos = [
                $contact_type_email,
                $contact_type_mobile,
                $contact_type_location,
                $contact_type_additional,
            ];

            $contacts_types_dtos = new ContactsTypesDTO();
            $contacts_types_dtos->setContactTypeDtos($contact_type_dtos);
            $contacts_types_json = $contacts_types_dtos->toJson();

            $contact = new MyContact();
            $contact->setName($person_name);
            $contact->setContacts($contacts_types_json);
            $contact->setDescription($person_nickname);

            $color_set = Utils::arrayGetRandom(Contact::ALL_COLORS_SETS);
            $description_background_color = $color_set[Contact::KEY_DESCRIPTION_BACKGROUND_COLOR];
            $name_background_color        = $color_set[Contact::KEY_NAME_BACKGROUND_COLOR];

            $contact->setImagePath($person_picture);
            $contact->setGroup($person_group);
            $contact->setDescriptionBackgroundColor($description_background_color);
            $contact->setNameBackgroundColor($name_background_color);

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
        $all_groups = $this->app->repositories->myContactGroupRepository->findAll();
        $group      = Utils::arrayGetRandom($all_groups);
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
