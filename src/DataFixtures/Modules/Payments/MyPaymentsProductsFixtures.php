<?php

namespace App\DataFixtures\Modules\Payments;

use App\Controller\Utils\Utils;
use App\DataFixtures\Providers\Business\Shops;
use App\DataFixtures\Providers\Products\Domestic;
use App\DataFixtures\Providers\Products\Food;
use App\Entity\Modules\Payments\MyPaymentsProduct;
use Bezhanov\Faker\ProviderCollectionHelper;
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
        ProviderCollectionHelper::addAllProvidersTo($this->faker);
    }

    public function load(ObjectManager $manager)
    {

        $all_food_products       = (new Food())->all;
        $all_food_shops          = Shops::SUPERMARKETS;

        $all_domestic_products   = (new Domestic())->all;
        $all_domestic_shops      = Shops::DOMESTIC_SHOPS;

        $this->addProductsWithShops($all_food_products, $all_food_shops, $manager);
        $this->addProductsWithShops($all_domestic_products, $all_domestic_shops, $manager);

        $manager->flush();
    }

    private function addProductsWithShops(array $all_products, array $all_shops, ObjectManager $manager){
        foreach($all_products as $product_name){

            $shop     = Utils::arrayGetRandom($all_shops);

            $price    = $this->faker->randomFloat(2, 2, 10);
            $rejected = $this->faker->boolean;

            $product = new MyPaymentsProduct();
            $product->setName($product_name);
            $product->setInformation('');
            $product->setMarket($shop);
            $product->setPrice($price);
            $product->setRejected($rejected);

            $manager->persist($product);

        }

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
