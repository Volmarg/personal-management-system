<?php

namespace App\DataFixtures\Modules\Passwords;

use App\DataFixtures\Providers\Modules\PasswordsGroups;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyPasswordsSettingsFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory $faker
     */
    private $faker;

    /**
     * @var PasswordsGroups $passwords_groups_provider
     */
    private $passwords_groups_provider;

    public function __construct() {
        $this->faker                     = Factory::create('en');
        $this->passwords_groups_provider = new PasswordsGroups();

    }

    public function load(ObjectManager $manager)
    {

        foreach($this->passwords_groups_provider::ALL as $password_group_name) {
            $passwordGroup = new MyPasswordsGroups();
            $passwordGroup->setName($password_group_name);

            $manager->persist($passwordGroup);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 0;
    }
}
