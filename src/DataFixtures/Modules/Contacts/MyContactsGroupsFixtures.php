<?php

namespace App\DataFixtures\Modules\Contacts;

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

    public function __construct() {
        $this->faker = Factory::create('en');

    }

    public function load(ObjectManager $manager)
    {
        for($x = 0; $x <= 10; $x++) {

            $name               = $this->faker->word;

            $my_contact_group  = new MyContactsGroups();
            $my_contact_group->setName($name);

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
