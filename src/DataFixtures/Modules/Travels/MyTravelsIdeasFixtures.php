<?php

namespace App\DataFixtures\Modules\Travels;

use App\Controller\Utils\Utils;
use App\DataFixtures\Providers\Modules\Travels;
use App\Entity\Modules\Travels\MyTravelsIdeas;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyTravelsIdeasFixtures extends Fixture
{
    /**
     * Factory $faker
     */
    private $faker;

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


            $country    = $this->faker->country;
            $city       = $this->faker->city;
            $category   = Utils::arrayGetRandom($categories);
            $image      = Utils::arrayGetRandom(Travels::IMAGES_IN_PUBLIC_DIR);
            $map        = Utils::arrayGetRandom(Travels::MAPS);

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
