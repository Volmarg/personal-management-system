<?php

namespace App\DataFixtures\Modules\Job;

use App\Controller\Utils\Utils;
use App\DataFixtures\Providers\Modules\JobAfterhours;
use App\DataFixtures\Providers\Modules\JobHolidays;
use App\Entity\Modules\Job\MyJobAfterhours;
use App\Entity\Modules\Job\MyJobHolidays;
use App\Entity\Modules\Job\MyJobHolidaysPool;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
        foreach(JobHolidays::ALL_COMPANIES as $company_name => $holiday_data){
            $year = $holiday_data[JobHolidays::KEY_YEAR];
            $days = $holiday_data[JobHolidays::KEY_HOLIDAYS_COUNT];

            $my_job_holidays_pool = new MyJobHolidaysPool();
            $my_job_holidays_pool->setCompanyName($company_name);
            $my_job_holidays_pool->setDaysLeft($days);
            $my_job_holidays_pool->setYear($year);

            $manager->persist($my_job_holidays_pool);
            $manager->flush();
        }

        // now add spent days
        $index = 0;
        foreach(JobHolidays::ALL_COMPANIES as $company_name => $holiday_data){
            $daysMin = 5;
            $daysMax = $holiday_data[JobHolidays::KEY_HOLIDAYS_COUNT];

            $year    = $holiday_data[JobHolidays::KEY_YEAR];
            $days    = $this->faker->randomFloat(0, $daysMin, $daysMax);
            $info    = JobHolidays::ALL_REASONS[$index];

            $my_job_holidays_pool = new MyJobHolidays();
            $my_job_holidays_pool->setInformation($info);
            $my_job_holidays_pool->setDaysSpent($days);
            $my_job_holidays_pool->setYear($year);

            $manager->persist($my_job_holidays_pool);
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
