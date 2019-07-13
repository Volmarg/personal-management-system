<?php

namespace App\DataFixtures\Modules\Passwords;

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
        $passwords_groups = $manager->getRepository(MyPasswordsGroups::class)->findAll();

        for($x = 0 ;$x <= 20; $x++){

            $index           = array_rand($passwords_groups);
            $password_group  = $passwords_groups[$index];

            $password_string = $this->faker->password;

            $description     = $this->faker->sentence;
            $url             = $this->faker->url;
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
