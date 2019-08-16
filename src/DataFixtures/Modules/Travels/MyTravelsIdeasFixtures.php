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

    /**
     * @var Finder $finder
     */
    private $finder;

    const MAPS = [
        'http://www.mapcrunch.com/p/41.337537_19.866791_46.94_0.99_-1',
        'http://www.mapcrunch.com/p/56.312984_25.238508_-301.48_-5_0',
        'http://www.mapcrunch.com/p/38.338997_-81.689773_47.52_-5_0',
        'http://www.mapcrunch.com/p/-17.661214_-70.887061_172.52_-5_0',
        'http://www.mapcrunch.com/p/52.445403_-6.393284_-227.81_-5_0',
        'http://www.mapcrunch.com/p/-14.892871_-70.588343_-163.81_-5_0'
    ];

    public function __construct() {
        $this->faker    = Factory::create('en');
        $this->finder   = new Finder();
    }

    public function load(ObjectManager $manager)
    {
        $categories = [];
        $images     = [];

        $this->finder->files()->in(__DIR__.'/../../../../public/assets/images/modules/travels');

        foreach( $this->finder as $image ){
            $images[] = $image->getFilename();
        }

        for($x = 0; $x <= 6; $x++){
            $categories[] = $this->faker->country;
        }


        for($x = 0; $x <= 150; $x++){

            $index      = array_rand($categories);
            $category   = $categories[$index];

            $country    = $this->faker->country;
            $city       = $this->faker->city;

            $index      = array_rand($images);
            $image      = $images[$index];

            $index      = array_rand(static::MAPS);
            $map        = static::MAPS[$index];

            $travel_idea = new MyTravelsIdeas();
            $travel_idea->setCategory($category);
            $travel_idea->setMap($map);
            $travel_idea->setImage('/assets/images/modules/travels/'.$image);
            $travel_idea->setLocation($city);
            $travel_idea->setCountry($country);

            $manager->persist($travel_idea);
        }
        $manager->flush();
    }
}
