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

    /**
     * @var ExpensiveProducts $provider_expensive_products
     */
    private $provider_expensive_products;

    public function __construct() {
        $this->faker                         = Factory::create('en');
        $this->provider_expensive_products   = new ExpensiveProducts();
    }

    public function load(ObjectManager $manager)
    {

        foreach($this->provider_expensive_products::ALL as $product_name => $product_example) {

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
