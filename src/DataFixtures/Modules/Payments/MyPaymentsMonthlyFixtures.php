<?php

namespace App\DataFixtures\Modules\Payments;

use App\Controller\Utils\Utils;
use App\DataFixtures\Providers\Business\Shops;
use App\DataFixtures\Providers\Modules\PaymentsMonthly;
use App\DataFixtures\Providers\Modules\PaymentsSettings;
use App\DataFixtures\Providers\Products\Domestic;
use App\DataFixtures\Providers\Products\Food;
use App\Entity\Modules\Payments\MyPaymentsMonthly;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MyPaymentsMonthlyFixtures extends Fixture implements OrderedFixtureInterface
{

    const AMOUNT_OF_MONTH_TO_FILL       = 5;
    const AMOUNT_OF_PRODUCTS            = 4;
    const AMOUNT_OF_SHOPPING_PER_MONTH  = 8;

    /**
     * Factory $faker
     */
    private $faker;

    /**
     * @var Food
     */
    private $provider_food;

    /**
     * @var MyPaymentsSettingsRepository
     */
    private $payments_settings_repository;

    /**
     * @var ObjectManager $manager
     */
    private $manager;

    public function __construct() {
        $this->faker         = Factory::create('en');
        $this->provider_food = new Food();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $this->payments_settings_repository = $manager->getRepository(MyPaymentsSettings::class);
        $this->manager                      = $manager;

        $currDate = new \DateTime();

        for($x = 0; $x <= static::AMOUNT_OF_MONTH_TO_FILL ; $x++){
            $curr_month  = $currDate->format('m');
            $curr_year   = $currDate->format('y');

            # first recurring monthly payments
            $this->addRecurringPayments( $curr_year, $curr_month);

            # now add some random FOOD products for each month
            $category_name  = PaymentsSettings::CATEGORY_FOOD;
            $shops_names    = Shops::SUPERMARKETS;
            $products_names = $this->provider_food->all;
            $this->addProductsListWithShop($curr_year, $curr_month, $category_name, $shops_names, $products_names);

            # now add some random DOMESTIC products for each month
            $category_name  = PaymentsSettings::CATEGORY_DOMESTIC;
            $shops_names    = Shops::DOMESTIC_SHOPS;
            $products_names = (new Domestic())->all;
            $this->addProductsListWithShop($curr_year, $curr_month, $category_name, $shops_names, $products_names);

            $currDate->modify('+1months');

        }

        $manager->flush();
    }

    /**
     * @param int $curr_year
     * @param int $curr_month
     * @throws \Exception
     */
    private function addRecurringPayments(int $curr_year, int $curr_month) {
        foreach (PaymentsMonthly::ALL_MONTHLY as $name => $price) {

            $date = "1-{$curr_month}-{$curr_year}";

            $firstDayOfMonthDateTime = new \DateTime($date);

            $monthly_payments_types  = $this->payments_settings_repository->findBy(['name' => PaymentsSettings::CATEGORY_MONTHLY_PAYMENTS]);
            $monthly_payments_type   = reset($monthly_payments_types);

            $monthlyPayment = new MyPaymentsMonthly();
            $monthlyPayment->setType($monthly_payments_type);
            $monthlyPayment->setDate($firstDayOfMonthDateTime);
            $monthlyPayment->setDescription($name);
            $monthlyPayment->setMoney($price);

            $this->manager->persist($monthlyPayment);
        }
    }

    /**
     * @param int $curr_year
     * @param int $curr_month
     * @param string $category_name
     * @param array $shops_names
     * @param array $products_names
     * @throws \Exception
     */
    private function addProductsListWithShop(
        int             $curr_year,
        int             $curr_month,
        string          $category_name,
        array           $shops_names,
        array           $products_names

    ){
        for($y = 0; $y <= static::AMOUNT_OF_SHOPPING_PER_MONTH; $y++) {

            $day        = rand(1, 25);
            $date       = "{$day}-{$curr_month}-{$curr_year}";
            $dateTime   = new \DateTime($date);

            $monthly_payments_types  = $this->payments_settings_repository->findBy(['name' => $category_name]);
            $monthly_payments_type   = reset($monthly_payments_types);


            $products_list  = '';
            $shop_name      = Utils::arrayGetRandom($shops_names);
            $products       = Utils::arrayGetNotRepeatingValuesCount($products_names, static::AMOUNT_OF_PRODUCTS);
            $products_count = count($products) -1;

            foreach($products as $index => $product){

                $products_list .= $product;

                if( $index < $products_count) {
                    $products_list .= ',';
                }
            }

            $description = "{$shop_name}: $products_list";
            $money       = rand(100, 1599) / 10;


            $monthlyPayment = new MyPaymentsMonthly();
            $monthlyPayment->setType($monthly_payments_type);
            $monthlyPayment->setDate($dateTime);
            $monthlyPayment->setDescription($description);
            $monthlyPayment->setMoney($money);

            $this->manager->persist($monthlyPayment);
        }

    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder() {
        return 5;
    }
}
