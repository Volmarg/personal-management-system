<?php

namespace App\DataFixtures\Modules\Car;

use App\DataFixtures\Providers\Modules\CarSchedules;
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

    /**
     * @var CarSchedules
     */
    private $car_schedules_provider;

    public function __construct() {
        $this->faker                  = Factory::create('en');
        $this->car_schedules_provider = new CarSchedules();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        foreach($this->car_schedules_provider::ALL as $car_schedule_data)
        {
            $index         = array_rand(MyCarSchedulesTypesFixtures::TYPES);
            $schedule_type = MyCarSchedulesTypesFixtures::TYPES[$index];

            $date        = $this->faker->dateTimeBetween('+5 day','+9 month')->format('d-m-Y');
            $name        = $car_schedule_data[CarSchedules::KEY_NAME];
            $information = $car_schedule_data[CarSchedules::KEY_INFORMATION];

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
