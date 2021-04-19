<?php

namespace App\DataFixtures\Modules\Job;

use App\DataFixtures\Providers\Modules\JobHolidays;
use App\Entity\Modules\Job\MyJobHolidays;
use App\Entity\Modules\Job\MyJobHolidaysPool;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MyJobHolidaysFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory $faker
     */
    private $faker;

    public function __construct() {
        $this->faker = Factory::create('en');
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        // first add pools
        foreach(JobHolidays::ALL_COMPANIES as $companyName => $holidayData){
            $year = $holidayData[JobHolidays::KEY_YEAR];
            $days = $holidayData[JobHolidays::KEY_HOLIDAYS_COUNT];

            $myJobHolidaysPool = new MyJobHolidaysPool();
            $myJobHolidaysPool->setCompanyName($companyName);
            $myJobHolidaysPool->setDaysInPool($days);
            $myJobHolidaysPool->setYear($year);

            $manager->persist($myJobHolidaysPool);
            $manager->flush();
        }

        // now add spent days
        $index = 0;
        foreach(JobHolidays::ALL_COMPANIES as $companyName => $holidayData){
            $daysMin = 5;
            $daysMax = $holidayData[JobHolidays::KEY_HOLIDAYS_COUNT];

            $year    = $holidayData[JobHolidays::KEY_YEAR];
            $days    = $this->faker->randomFloat(0, $daysMin, $daysMax);
            $info    = JobHolidays::ALL_REASONS[$index];

            $myJobHolidaysPool = new MyJobHolidays();
            $myJobHolidaysPool->setInformation($info);
            $myJobHolidaysPool->setDaysSpent($days);
            $myJobHolidaysPool->setYear($year);

            $manager->persist($myJobHolidaysPool);
            $manager->flush();

            $index++;
        }

    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 9;
    }
}
