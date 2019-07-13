<?php

namespace App\DataFixtures\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyPaymentsProductsFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Factory|$faker
     */
    private $faker;

    public function __construct() {
        $this->faker = Factory::create('en');
        \Bezhanov\Faker\ProviderCollectionHelper::addAllProvidersTo($this->faker);
    }

    public function load(ObjectManager $manager)
    {

        for($x = 0; $x <= 50; $x++) {

            $product = new MyPaymentsProduct();

            $example_products_arr = [];
            $counter              = $this->faker->numberBetween(0, 5);

            for($z = 0; $z <= $counter; $z++){
                $products[] = $this->faker->word;
            }

            $example_products     = implode(', ', $example_products_arr);
            $name                 = $this->faker->productName;
            $information          = $this->faker->sentence;
            $market               = $this->faker->word;
            $price                = $this->faker->randomFloat(2, 2, 80);
            $rejected             = $this->faker->boolean;

            $product->setName($name);
            $product->setInformation($information);
            $product->setMarket($market);
            $product->setPrice($price);
            $product->setRejected($rejected);
            $product->setProducts($example_products);

            $manager->persist($product);

        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 6;
    }
}
