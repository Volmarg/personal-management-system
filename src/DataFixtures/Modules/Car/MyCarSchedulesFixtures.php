<?php

namespace App\DataFixtures\Modules\Car;

use App\Entity\Modules\Car\MyCar;
use App\Entity\Modules\Car\MyCarSchedulesTypes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyCarSchedulesFixtures extends Fixture implements OrderedFixtureInterface
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

        $schedules_types        = $manager->getRepository(MyCarSchedulesTypes::class)->findAll();

        for($x = 0; $x <= 10; $x++) {

            $index              = array_rand($schedules_types);
            $schedule_type      = $schedules_types[$index];

            $name               = $this->faker->word;
            $date               = $this->faker->dateTimeBetween('+5 day','+9 month')->format('d-m-Y');
            $information        = $this->faker->text(130);

            $car_schedule  = new MyCar();
            $car_schedule->setName($name);
            $car_schedule->setScheduleType($schedule_type);
            $car_schedule->setInformation($information);
            $car_schedule->setDate($date);


            $manager->persist($car_schedule);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 16;
    }
}
