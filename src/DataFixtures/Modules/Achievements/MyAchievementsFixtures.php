<?php

namespace App\DataFixtures\Modules\Achievements;

use App\DataFixtures\Providers\Modules\Achievements;
use App\Entity\Modules\Achievements\Achievement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MyAchievementsFixtures extends Fixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {

        foreach (Achievements::ALL as $type => $achievements) {
            foreach($achievements as $index => $name) {
                $achievement  = new Achievement();
                $achievement->setName($name);
                $achievement->setType($type);
                $manager->persist($achievement);
            }
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
