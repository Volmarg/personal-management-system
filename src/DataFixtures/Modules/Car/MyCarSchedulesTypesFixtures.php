<?php

namespace App\DataFixtures\Modules\Car;

use App\Entity\Modules\Car\MyCarSchedulesTypes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyCarSchedulesTypesFixtures extends Fixture implements OrderedFixtureInterface
{

    const TYPE_RECURRING = 'recurring';
    const TYPE_ONE_TIME  = 'one-time';

    const TYPES = [
      self::TYPE_RECURRING,
      self::TYPE_ONE_TIME,
    ];

    /**
     * Factory $faker
     */
    private $faker;

    public function __construct() {
        $this->faker = Factory::create('en');

    }


    public function load(ObjectManager $manager)
    {

        for($x = 0; $x <= count(static::TYPES) -1; $x++) {

            $name = static::TYPES[$x];

            $car_schedule_type = new MyCarSchedulesTypes();
            $car_schedule_type->setName($name);

            $manager->persist($car_schedule_type);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 15;
    }

}
