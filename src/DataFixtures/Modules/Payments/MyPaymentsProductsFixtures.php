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
use Doctrine\Persistence\ObjectManager;
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

        $allFoodProducts     = (new Food())->all;
        $allFoodShops        = Shops::SUPERMARKETS;

        $allDomesticProducts = (new Domestic())->all;
        $allDomesticShops    = Shops::DOMESTIC_SHOPS;

        $this->addProductsWithShops($allFoodProducts, $allFoodShops, $manager);
        $this->addProductsWithShops($allDomesticProducts, $allDomesticShops, $manager);

        $manager->flush();
    }

    private function addProductsWithShops(array $allProducts, array $allShops, ObjectManager $manager){
        foreach($allProducts as $productName){

            $shop     = Utils::arrayGetRandom($allShops);

            $price    = $this->faker->randomFloat(2, 2, 10);
            $rejected = $this->faker->boolean;

            $product = new MyPaymentsProduct();
            $product->setName(ucfirst($productName));
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
