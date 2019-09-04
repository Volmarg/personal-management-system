<?php

namespace App\DataFixtures\Modules\Job;

use App\DataFixtures\Providers\Modules\JobAfterhours;
use App\Entity\Modules\Job\MyJobAfterhours;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyJobAfterhoursFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory $faker
     */
    private $faker;

    /**
     * @var JobAfterhours
     */
    private $provider_job_afterhours;


    public function __construct() {
        $this->faker                    = Factory::create('en');
        $this->provider_job_afterhours  = new JobAfterhours();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        for($x = 0; $x <= 25; $x++) {

            $index              = array_rand($this->provider_job_afterhours::ALL_GOALS);
            $goal               = $this->provider_job_afterhours::ALL_GOALS[$index];

            $date               = $this->faker->date();

            $index              = array_rand(MyJobAfterhours::ALL_TYPES);
            $type               = MyJobAfterhours::ALL_TYPES[$index];

            $minutes            = $this->faker->numberBetween(1, 80);
            $description        = $this->faker->text(20);

            $my_job_afterhours  = new MyJobAfterhours();
            $my_job_afterhours->setDate($date);
            $my_job_afterhours->setGoal($goal);
            $my_job_afterhours->setType($type);
            $my_job_afterhours->setMinutes($minutes);
            $my_job_afterhours->setDescription($description);

            $manager->persist($my_job_afterhours);
        }

        $manager->flush();
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
