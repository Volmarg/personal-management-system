<?php

namespace App\DataFixtures\Modules\Shopping;

use App\DataFixtures\Providers\Products\ExpensiveProducts;
use App\Entity\Modules\Shopping\MyShoppingPlans;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
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

        foreach(ExpensiveProducts::ALL as $productName => $productExample) {

            $shoppingPlan = new MyShoppingPlans();
            $shoppingPlan->setName(ucfirst($productName));
            $shoppingPlan->setExample($productExample);
            $shoppingPlan->setInformation("");

            $manager->persist($shoppingPlan);

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
