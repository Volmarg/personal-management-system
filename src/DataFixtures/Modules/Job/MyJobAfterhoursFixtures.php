<?php

namespace App\DataFixtures\Modules\Job;

use App\Controller\Utils\Utils;
use App\DataFixtures\Providers\Modules\JobAfterhours;
use App\Entity\Modules\Job\MyJobAfterhours;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
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

            $date        = $this->faker->date();
            $minutesMade = $this->faker->numberBetween(45, 80);

            $hoursFromMinutesMade = floor($minutesMade / 60);
            $minutesLeftFromHours = $minutesMade % 60;
            $minutesLeftFromHours = ( $minutesLeftFromHours < 10 ? "0{$minutesLeftFromHours}" : $minutesLeftFromHours );

            $startHour  = rand(5, 8);
            $finishHour = $startHour + 8 + $hoursFromMinutesMade;

            $description = str_replace(
                JobAfterhours::STARTED_AT,
                "{$startHour}:00",
                JobAfterhours::DESCRIPTION_FROM_TO
            );

            $description = str_replace(
                JobAfterhours::FINISHED_AT,
                "{$finishHour}:{$minutesLeftFromHours}",
                $description
            );

            $myJobAfterhours  = new MyJobAfterhours();
            $myJobAfterhours->setDate($date);
            $myJobAfterhours->setGoal(ucfirst($goal));
            $myJobAfterhours->setType(MyJobAfterhours::TYPE_MADE);
            $myJobAfterhours->setMinutes($minutesMade);
            $myJobAfterhours->setDescription($description);

            $manager->persist($myJobAfterhours);
            $manager->flush();
        }

        // I want to have less spent
        for($x = 0; $x <= 10; $x++) {

            // this is because i want to add spent time only to goals for which i collected some time
            $allGoalsNames = $manager->getRepository(MyJobAfterhours::class)->getAllGoalsNames();
            $goal          = Utils::arrayGetRandom($allGoalsNames);

            $date          = $this->faker->date();
            $minutesSpent  = $this->faker->numberBetween(10, 30);

            $myJobAfterhours  = new MyJobAfterhours();
            $myJobAfterhours->setDate($date);
            $myJobAfterhours->setGoal(ucfirst($goal));
            $myJobAfterhours->setType(MyJobAfterhours::TYPE_SPENT);
            $myJobAfterhours->setMinutes($minutesSpent);
            $myJobAfterhours->setDescription('');

            $manager->persist($myJobAfterhours);
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
