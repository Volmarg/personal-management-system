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
        foreach(Schedules::ALL_SCHEDULES_TYPES as $scheduleTypeData){
            $name = $scheduleTypeData[Schedules::KEY_NAME];
            $icon = $scheduleTypeData[Schedules::KEY_ICON];

            $scheduleType = new MyScheduleType();
            $scheduleType->setName($name);
            $scheduleType->setIcon($icon);

            $manager->persist($scheduleType);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function addSchedules(ObjectManager $manager){

        $allSchedulesData = array_merge(Schedules::ALL_CAR_SCHEDULES, Schedules::ALL_HOME_SCHEDULES);
        foreach($allSchedulesData as $scheduleData)
        {

            $typeName     = $scheduleData[Schedules::KY_SCHEDULE_TYPE_NAME];
            $type          = $this->app->repositories->myScheduleTypeRepository->findOneBy(["name" => $typeName]);

            $name          = $scheduleData[Schedules::KEY_NAME];
            $information   = $scheduleData[Schedules::KEY_INFORMATION];
            $date          = $this->faker->dateTimeBetween('+5 day','+2 month')->format('d-m-Y');

            $carSchedule  = new MySchedule();
            $carSchedule->setName($name);
            $carSchedule->setScheduleType($type);
            $carSchedule->setInformation($information);
            $carSchedule->setDate($date);
            $carSchedule->setIsDateBased();

            $manager->persist($carSchedule);
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
