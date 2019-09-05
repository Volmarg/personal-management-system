<?php

namespace App\DataFixtures\Modules\Contacts;

use App\DataFixtures\Providers\Modules\ContactGroups;
use App\Entity\Modules\Contacts\MyContactsGroups;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MyContactsGroupsFixtures extends Fixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {

        foreach(ContactGroups::ALL as $contact_group_name) {
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
