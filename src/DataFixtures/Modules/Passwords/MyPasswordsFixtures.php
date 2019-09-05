<?php

namespace App\DataFixtures\Modules\Passwords;

use App\DataFixtures\Providers\Modules\Passwords;
use App\Entity\Modules\Passwords\MyPasswords;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyPasswordsFixtures extends Fixture implements OrderedFixtureInterface
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

        foreach(Passwords::ALL as $password){

            $url              = $password[Passwords::KEY_URL];
            $description      = $password[Passwords::KEY_DESCRIPTION];
            $group_name       = $password[Passwords::KEY_GROUP];

            $passwords_groups = $manager->getRepository(MyPasswordsGroups::class)->findBy(['name' => $group_name]);
            $password_group   = reset($passwords_groups);

            $password_string = $this->faker->password;
            $login           = $this->faker->word;

            $password = new MyPasswords();
            $password->setPassword($password_string);
            $password->setDescription($description);
            $password->setGroup($password_group);
            $password->setUrl($url);
            $password->setLogin($login);

            $manager->persist($password);

        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 1;
    }
}
