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
use Doctrine\Persistence\ObjectManager;
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
    private $providerFood;

    /**
     * @var MyPaymentsSettingsRepository
     */
    private $paymentsSettingsRepository;

    /**
     * @var ObjectManager $manager
     */
    private $manager;

    public function __construct() {
        $this->faker        = Factory::create('en');
        $this->providerFood = new Food();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $this->paymentsSettingsRepository = $manager->getRepository(MyPaymentsSettings::class);
        $this->manager                      = $manager;

        $currDate = new \DateTime();

        for($x = 0; $x <= static::AMOUNT_OF_MONTH_TO_FILL ; $x++){
            $currMonth  = $currDate->format('m');
            $currYear   = $currDate->format('y');

            # first recurring monthly payments
            $this->addRecurringPayments( $currYear, $currMonth);

            # now add some random FOOD products for each month
            $categoryName  = PaymentsSettings::CATEGORY_FOOD;
            $shopsNames    = Shops::SUPERMARKETS;
            $productsNames = $this->providerFood->all;
            $this->addProductsListWithShop($currYear, $currMonth, $categoryName, $shopsNames, $productsNames);

            # now add some random DOMESTIC products for each month
            $categoryName  = PaymentsSettings::CATEGORY_DOMESTIC;
            $shopsNames    = Shops::DOMESTIC_SHOPS;
            $productsNames = (new Domestic())->all;
            $this->addProductsListWithShop($currYear, $currMonth, $categoryName, $shopsNames, $productsNames);

            $currDate->modify('+1months');

        }

        $manager->flush();
    }

    /**
     * @param int $currYear
     * @param int $currMonth
     * @throws \Exception
     */
    private function addRecurringPayments(int $currYear, int $currMonth) {
        foreach (PaymentsMonthly::ALL_MONTHLY as $name => $price) {

            $date = "{$currYear}-{$currMonth}-1";

            $firstDayOfMonthDateTime = new \DateTime($date);

            $monthlyPaymentsTypes  = $this->paymentsSettingsRepository->findBy(['value' => PaymentsSettings::CATEGORY_MONTHLY_PAYMENTS]);
            $monthlyPaymentsType   = reset($monthlyPaymentsTypes);

            $monthlyPayment = new MyPaymentsMonthly();
            $monthlyPayment->setType($monthlyPaymentsType);
            $monthlyPayment->setDate($firstDayOfMonthDateTime);
            $monthlyPayment->setDescription($name);
            $monthlyPayment->setMoney($price);

            $this->manager->persist($monthlyPayment);
        }
    }

    /**
     * @param int $currYear
     * @param int $currMonth
     * @param string $categoryName
     * @param array $shopsNames
     * @param array $productsNames
     * @throws \Exception
     */
    private function addProductsListWithShop(
        int    $currYear,
        int    $currMonth,
        string $categoryName,
        array  $shopsNames,
        array  $productsNames
    ){
        for($y = 0; $y <= static::AMOUNT_OF_SHOPPING_PER_MONTH; $y++) {

            $day        = rand(1, 25);
            $date       = "{$currYear}-{$currMonth}-{$day}";
            $dateTime   = new \DateTime($date);

            $monthlyPaymentsTypes  = $this->paymentsSettingsRepository->findBy(['value' => $categoryName]);
            $monthlyPaymentsType   = reset($monthlyPaymentsTypes);


            $productsList  = '';
            $shopName      = Utils::arrayGetRandom($shopsNames);
            $products      = Utils::arrayGetNotRepeatingValuesCount($productsNames, static::AMOUNT_OF_PRODUCTS);
            $productsCount = count($products) -1;

            foreach($products as $index => $product){

                $productsList .= $product;

                if( $index < $productsCount) {
                    $productsList .= ', ';
                }
            }

            $description = "{$shopName}: $productsList";
            $money       = rand(100, 1599) / 10;


            $monthlyPayment = new MyPaymentsMonthly();
            $monthlyPayment->setType($monthlyPaymentsType);
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
