<?php

namespace App\DataFixtures\Modules\Contacts;

use App\DataFixtures\Providers\Modules\ContactGroups;
use App\Entity\Modules\Contacts\MyContactsGroups;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyContactsGroupsFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory $faker
     */
    private $faker;

    /**
     * @var ContactGroups provider_contact_groups
     */
    private $provider_contact_groups;

    public function __construct() {
        $this->faker                   = Factory::create('en');
        $this->provider_contact_groups = new ContactGroups();

    }

    public function load(ObjectManager $manager)
    {

        foreach($this->provider_contact_groups::ALL as $contact_group_name) {
            $my_contact_group = new MyContactsGroups();
            $my_contact_group->setName($contact_group_name);

            $manager->persist($my_contact_group);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 13;
    }
}
