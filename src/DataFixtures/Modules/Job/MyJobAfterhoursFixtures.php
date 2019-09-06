<?php

namespace App\DataFixtures\Modules\Job;

use App\Controller\Utils\Utils;
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

    public function __construct() {
        $this->faker = Factory::create('en');
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        for($x = 0; $x <= 25; $x++) {

            $goal = Utils::arrayGetRandom(JobAfterhours::ALL_GOALS);

            $date           = $this->faker->date();
            $minutes_made   = $this->faker->numberBetween(45, 80);

            $hours_from_minutes_made = floor($minutes_made / 60);
            $minutes_left_from_hours = $minutes_made % 60;
            $minutes_left_from_hours = ( $minutes_left_from_hours < 10 ? "0{$minutes_left_from_hours}" : $minutes_left_from_hours );

            $startHour   = rand(5, 8);
            $finishHour  = $startHour + 8 + $hours_from_minutes_made;

            $description = str_replace(
                JobAfterhours::STARTED_AT,
                "{$startHour}:00",
                JobAfterhours::DESCRIPTION_FROM_TO
            );

            $description = str_replace(
                JobAfterhours::FINISHED_AT,
                "{$finishHour}:{$minutes_left_from_hours}",
                $description
            );

            $my_job_afterhours  = new MyJobAfterhours();
            $my_job_afterhours->setDate($date);
            $my_job_afterhours->setGoal(ucfirst($goal));
            $my_job_afterhours->setType(MyJobAfterhours::TYPE_MADE);
            $my_job_afterhours->setMinutes($minutes_made);
            $my_job_afterhours->setDescription($description);

            $manager->persist($my_job_afterhours);
            $manager->flush();
        }

        // I want to have less spent
        for($x = 0; $x <= 10; $x++) {

            // this is because i want to add spent time only to goals for which i collected some time
            $all_goals_names = $manager->getRepository(MyJobAfterhours::class)->getAllGoalsNames();
            $goal            = Utils::arrayGetRandom($all_goals_names);

            $date           = $this->faker->date();
            $minutes_spent  = $this->faker->numberBetween(10, 30);

            $my_job_afterhours  = new MyJobAfterhours();
            $my_job_afterhours->setDate($date);
            $my_job_afterhours->setGoal(ucfirst($goal));
            $my_job_afterhours->setType(MyJobAfterhours::TYPE_SPENT);
            $my_job_afterhours->setMinutes($minutes_spent);
            $my_job_afterhours->setDescription('');

            $manager->persist($my_job_afterhours);
            $manager->flush();
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
