<?php

namespace App\DataFixtures\Modules\Travels;

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

    const IMAGES = [
        'https://cbstampa.files.wordpress.com/2017/07/swissalps.jpg?w=625&h=403&crop=1',
        'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQtzlIk2HUReVbEug-1t8LsPASSBiVteBaW4SntOL0vP4kB7vYq',
        'https://1dib1q3k1s3e11a5av3bhlnb-wpengine.netdna-ssl.com/wp-content/uploads/2013/04/cochem-town-germany.jpg',
        'http://thewowstyle.com/wp-content/uploads/2015/01/free-beautiful-place-wallpaper-hd-116.jpg',
        'https://media.cntraveler.com/photos/59a06e62cd44216d03660fd8/master/w_420,c_limit/Twelve-Apostles-GettyImages-459760175.jpg',
        'https://cloud.lovin.ie/images/uploads/2017/07/_featuredImage/shutterstock_431693263.jpg?mtime=20170717124617%20880w',
        'https://allthatsinteresting.com/wordpress/wp-content/uploads/2014/12/jasper-national-park-medicine-lake.jpg',
        'https://backpackeradvice.com/img/moraine_lake_banff.jpg',
        'http://tripstodiscover.com/wp-content/uploads/2014/06/Cinque-Tierre.jpg',
        'https://www.worldwalks.com/wp-content/uploads/2018/01/canada-2-760x440.jpg',
        'https://1.bp.blogspot.com/-4UZMvwoThO4/XQulOhOcOQI/AAAAAAAAAPI/ok0U6kEub4EXZT7qLGwAQCHy9-683g91wCLcBGAs/s1600/best%2Bplaces%2Bto%2Bvisit%2Bnear%2BAmritsar.jpeg',
        'https://d3hne3c382ip58.cloudfront.net/files/uploads/bookmundi/resized/cms/hong-kong-asia-1525428283-1000X561.jpg'
    ];

    const MAPS = [
        'http://www.mapcrunch.com/p/41.337537_19.866791_46.94_0.99_-1',
        'http://www.mapcrunch.com/p/56.312984_25.238508_-301.48_-5_0',
        'http://www.mapcrunch.com/p/38.338997_-81.689773_47.52_-5_0',
        'http://www.mapcrunch.com/p/-17.661214_-70.887061_172.52_-5_0',
        'http://www.mapcrunch.com/p/52.445403_-6.393284_-227.81_-5_0',
        'http://www.mapcrunch.com/p/-14.892871_-70.588343_-163.81_-5_0'
    ];

    public function __construct() {
        $this->faker = Factory::create('en');

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

            $index      = array_rand(static::IMAGES);
            $image      = static::IMAGES[$index];

            $index      = array_rand(static::MAPS);
            $map        = static::MAPS[$index];

            $travel_idea = new MyTravelsIdeas();
            $travel_idea->setCategory($category);
            $travel_idea->setMap($map);
            $travel_idea->setImage($image);
            $travel_idea->setLocation($city);
            $travel_idea->setCountry($country);

            $manager->persist($travel_idea);
        }
        $manager->flush();
    }
}
