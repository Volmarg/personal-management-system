<?php

namespace App\DataFixtures\Modules\Schedule;

use App\Controller\Core\Application;
use App\DataFixtures\Providers\Modules\Schedules;
use App\Entity\Modules\Schedules\MySchedule;
use App\Entity\Modules\Schedules\MyScheduleType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyScheduleFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory $faker
     */
    private $faker;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->faker = Factory::create('en');
        $this->app   = $app;
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        $this->addScheduleTypes($manager);
        $this->addSchedules($manager);
    }

    private function addScheduleTypes(ObjectManager $manager){
        foreach(Schedules::ALL_SCHEDULES_TYPES as $schedule_type_data){
            $name = $schedule_type_data[Schedules::KEY_NAME];
            $icon = $schedule_type_data[Schedules::KEY_ICON];

            $schedule_type = new MyScheduleType();
            $schedule_type->setName($name);
            $schedule_type->setIcon($icon);

            $manager->persist($schedule_type);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function addSchedules(ObjectManager $manager){

        $all_schedules_data = array_merge(Schedules::ALL_CAR_SCHEDULES, Schedules::ALL_HOME_SCHEDULES);

        foreach($all_schedules_data as $schedule_data)
        {

            $type_name     = $schedule_data[Schedules::KY_SCHEDULE_TYPE_NAME];
            $type          = $this->app->repositories->myScheduleTypeRepository->findOneBy(["name" => $type_name]);

            $name          = $schedule_data[Schedules::KEY_NAME];
            $information   = $schedule_data[Schedules::KEY_INFORMATION];
            $date          = $this->faker->dateTimeBetween('+5 day','+2 month')->format('d-m-Y');

            $car_schedule  = new MySchedule();
            $car_schedule->setName($name);
            $car_schedule->setScheduleType($type);
            $car_schedule->setInformation($information);
            $car_schedule->setDate($date);
            $car_schedule->setIsDateBased();

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
