<?php

namespace App\DataFixtures\Modules\Achievements;

use App\Entity\Modules\Achievements\Achievement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyAchievementsFixtures extends Fixture implements OrderedFixtureInterface
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
        $achievements_types = ['simple','medium','hard','hardcore'];

        for($x = 0; $x <= 10; $x++) {

            $index              = array_rand($achievements_types);
            $achievement_type   = $achievements_types[$index];

            $name               = $this->faker->word;
            $description        = $this->faker->text(150);

            $achievements       = new Achievement();
            $achievements->setName($name);
            $achievements->setType($achievement_type);
            $achievements->setDescription($description);


            $manager->persist($achievements);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 17;
    }
}
