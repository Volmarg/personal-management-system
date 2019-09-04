<?php

namespace App\DataFixtures\Modules\Achievements;

use App\DataFixtures\Providers\Modules\Achievements;
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

    /**
     * @var Achievements
     */
    private $provider_achievements;

    public function __construct() {
        $this->faker                 = Factory::create('en');
        $this->provider_achievements = new Achievements();
    }

    public function load(ObjectManager $manager)
    {

        foreach ($this->provider_achievements::ALL as $type => $name) {

            $achievements  = new Achievement();
            $achievements->setName($name);
            $achievements->setType($type);

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
