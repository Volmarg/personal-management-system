<?php

namespace App\DataFixtures\Modules\Travels;

use App\Entity\Modules\Travels\MyTravelsIdeas;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Finder\Finder;

class MyTravelsIdeasFixtures extends Fixture
{
    /**
     * Factory $faker
     */
    private $faker;

    const MAPS = [
        'http://www.mapcrunch.com/p/41.337537_19.866791_46.94_0.99_-1',
        'http://www.mapcrunch.com/p/56.312984_25.238508_-301.48_-5_0',
        'http://www.mapcrunch.com/p/38.338997_-81.689773_47.52_-5_0',
        'http://www.mapcrunch.com/p/-17.661214_-70.887061_172.52_-5_0',
        'http://www.mapcrunch.com/p/52.445403_-6.393284_-227.81_-5_0',
        'http://www.mapcrunch.com/p/-14.892871_-70.588343_-163.81_-5_0'
    ];

    const IMAGES_IN_PUBLIC_DIR = [
        "best+places+to+visit+near+Amritsar.jpeg","",
        "canada-2-760x440.jpg","",
        "Cinque-Tierre.jpg","",
        "cochem-town-germany.jpg","",
        "free-beautiful-place-wallpaper-hd-116.jpg","",
        "hong-kong-asia-1525428283-1000X561.jpg","",
        "jasper-national-park-medicine-lake.jpg","",
        "moraine_lake_banff.jpg","",
        "shutterstock_431693263.jpg","",
        "Twelve-Apostles-GettyImages-459760175.jpg",
    ];

    const IMAGES_PATH_IN_PUBLIC_DIR = '/assets/images/modules/travels/';

    public function __construct() {
        $this->faker    = Factory::create('en');
    }

    public function load(ObjectManager $manager)
    {
        $categories = [];

        for($x = 0; $x <= 6; $x++){
            $categories[] = $this->faker->country;
        }

        for($x = 0; $x <= 150; $x++){

            $index      = array_rand($categories);
            $category   = $categories[$index];

            $country    = $this->faker->country;
            $city       = $this->faker->city;

            $index      = array_rand(static::IMAGES_IN_PUBLIC_DIR);
            $image      = static::IMAGES_IN_PUBLIC_DIR[$index];

            $index      = array_rand(static::MAPS);
            $map        = static::MAPS[$index];

            $travel_idea = new MyTravelsIdeas();
            $travel_idea->setCategory($category);
            $travel_idea->setMap($map);
            $travel_idea->setImage(static::IMAGES_PATH_IN_PUBLIC_DIR.$image);
            $travel_idea->setLocation($city);
            $travel_idea->setCountry($country);

            $manager->persist($travel_idea);
        }
        $manager->flush();
    }
}
