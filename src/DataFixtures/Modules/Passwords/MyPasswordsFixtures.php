<?php

namespace App\DataFixtures\Modules\Passwords;

use App\DataFixtures\Providers\Modules\Passwords;
use App\Entity\Modules\Passwords\MyPasswords;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
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

            $url         = $password[Passwords::KEY_URL];
            $description = $password[Passwords::KEY_DESCRIPTION];
            $groupName   = $password[Passwords::KEY_GROUP];

            $passwordsGroups = $manager->getRepository(MyPasswordsGroups::class)->findBy(['name' => $groupName]);
            $passwordGroup   = reset($passwordsGroups);

            $passwordString = $this->faker->password;
            $login          = $this->faker->word;

            $password = new MyPasswords();
            $password->setPassword($passwordString);
            $password->setDescription($description);
            $password->setGroup($passwordGroup);
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
