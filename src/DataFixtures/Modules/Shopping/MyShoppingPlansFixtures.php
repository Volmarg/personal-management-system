<?php

namespace App\DataFixtures\Modules\Shopping;

use App\Entity\Modules\Shopping\MyShoppingPlans;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyShoppingPlansFixtures extends Fixture implements OrderedFixtureInterface
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

        for($x = 0; $x <= 15; $x++){

            $name           = $this->faker->word;
            $example        = $this->faker->word;
            $information    = $this->faker->text(100);

            $shopping_plan = new MyShoppingPlans();
            $shopping_plan->setName($name);
            $shopping_plan->setExample($example);
            $shopping_plan->setInformation($information);

            $manager->persist($shopping_plan);

        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 3;
    }
}
