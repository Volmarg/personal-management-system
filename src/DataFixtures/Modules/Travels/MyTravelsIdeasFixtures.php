<?php

namespace App\DataFixtures\Modules\Travels;

use App\Controller\Utils\Utils;
use App\DataFixtures\Providers\Modules\Travels;
use App\Entity\Modules\Travels\MyTravelsIdeas;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MyTravelsIdeasFixtures extends Fixture
{
    private const FAKE_IMAGE_PROVIDER = 'https://picsum.photos/700/300'; // the numbers are "width X height"

    /**
     * Factory $faker
     */
    private $faker;

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
            $map        = Utils::arrayGetRandom(Travels::MAPS);

            $travelIdea = new MyTravelsIdeas();
            $travelIdea->setCategory($category);
            $travelIdea->setMap($map);
            $travelIdea->setImage(self::FAKE_IMAGE_PROVIDER . "?hash=$x");
            $travelIdea->setLocation($city);
            $travelIdea->setCountry($country);

            $manager->persist($travelIdea);
        }
        $manager->flush();
    }
}
