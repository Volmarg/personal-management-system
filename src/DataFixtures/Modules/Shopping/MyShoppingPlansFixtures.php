<?php

namespace App\DataFixtures\Modules\Shopping;

use App\DataFixtures\Providers\Business\Shops;
use App\DataFixtures\Providers\Products\ExpensiveProducts;
use App\Entity\Modules\Shopping\MyShoppingPlans;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
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

        foreach(ExpensiveProducts::ALL as $product_name => $product_example) {

            $shopping_plan = new MyShoppingPlans();
            $shopping_plan->setName($product_name);
            $shopping_plan->setExample($product_example);
            $shopping_plan->setInformation("");

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
