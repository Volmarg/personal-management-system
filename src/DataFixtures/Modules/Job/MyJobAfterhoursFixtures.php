<?php

namespace App\DataFixtures\Modules\Job;

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

    public function load(ObjectManager $manager)
    {
        $goals = [NULL];
        $types = ['made', 'spent'];

        for($x = 0; $x <= 25; $x++) {

            $goals[]            = $this->faker->word;
            $index              = array_rand($goals);

            $goal               = $goals[$index];
            $date               = $this->faker->date();

            $index              = array_rand($types);
            $type               = $types[$index];

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
