<?php

namespace App\DataFixtures\Modules\Car;

use App\DataFixtures\Providers\Modules\CarSchedules;
use App\Entity\Modules\Car\MyCarSchedulesTypes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
//todo: adjust to schedules
class MyCarSchedulesTypesFixtures extends Fixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {

        for($x = 0; $x <= count(CarSchedules::TYPES) -1; $x++) {

            $name = CarSchedules::TYPES[$x];

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
