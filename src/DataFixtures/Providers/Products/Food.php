<?php
namespace App\DataFixtures\Providers\Products;

class Food{

    # Vegetables
    const FOOD_CARROT           = 'carrot';
    const FOOD_CUCUMBER         = 'cucumber';
    const FOOD_GARLIC           = 'garlic';
    const FOOD_BEANS_CAN        = 'beans can';
    const FOOD_TOMATO           = 'tomato';
    const FOOD_CABBAGE          = 'cabbage';
    const FOOD_SALAD            = 'salad';

    const VEGETABLES = [
        self::FOOD_CARROT,
        self::FOOD_CUCUMBER,
        self::FOOD_GARLIC,
        self::FOOD_BEANS_CAN,
        self::FOOD_TOMATO,
        self::FOOD_CABBAGE,
        self::FOOD_SALAD,
    ];

    # Fruits
    const FOOD_APPLE            = 'apple';
    const FOOD_BANANAS          = 'bananas';
    const FOOD_ORANGE           = 'orange';
    const FOOD_KIWI             = 'kiwi';
    const FOOD_CHERRIES         = 'cherries';

    const FRUITS = [
        self::FOOD_APPLE,
        self::FOOD_BANANAS,
        self::FOOD_ORANGE,
        self::FOOD_KIWI,
        self::FOOD_CHERRIES,
    ];

    # Meat
    const FOOD_CHICKEN_FILLET   = 'chicken fillet';
    const FOOD_HAM              = 'ham';

    const MEAT = [
        self::FOOD_CHICKEN_FILLET,
        self::FOOD_HAM,
    ];

    # Additions
    const FOOD_MAYO             = 'mayo';
    const FOOD_KETCHUP          = 'ketchup';
    const FOOD_SPICES           = 'spices';
    const FOOD_SUGAR            = 'sugar';

    const ADDITIONS = [
        self::FOOD_MAYO,
        self::FOOD_KETCHUP,
        self::FOOD_SPICES,
        self::FOOD_SUGAR,
    ];

    # Drinks
    const FOOD_COFFEE           = 'coffee';
    const FOOD_MILK             = 'milk';
    const FOOD_TEA              = 'tea';

    const DRINKS = [
        self::FOOD_COFFEE,
        self::FOOD_MILK,
        self::FOOD_TEA,
    ];

    # Other
    const FOOD_BREAD            = 'bread';
    const FOOD_YOGHURT          = 'yoghurt';
    const FOOD_BUTTER           = 'butter';
    const FOOD_CEREALS          = 'cereals';
    const FOOD_DARK_CHOCOLATE   = 'dark chocolate';
    const FOOD_WHITE_CHEESE     = 'white cheese';
    const FOOD_FRYING_OIL       = 'frying oil';

    const OTHER = [
        self::FOOD_BREAD,
        self::FOOD_YOGHURT,
        self::FOOD_BUTTER,
        self::FOOD_CEREALS,
        self::FOOD_DARK_CHOCOLATE,
        self::FOOD_WHITE_CHEESE,
        self::FOOD_FRYING_OIL,
    ];

    # all
    const KEY_GROUP_VEGETABLES  = 'vegetables';
    const KEY_GROUP_FRUITS      = 'fruits';
    const KEY_GROUP_MEAT        = 'meat';
    const KEY_GROUP_ADDITIONS   = 'additions';
    const KEY_GROUP_DRINKS      = 'drinks';
    const KEY_GROUP_OTHER       = 'other';

    const ALL_GROUPED = [
        self::KEY_GROUP_VEGETABLES  => self::VEGETABLES,
        self::KEY_GROUP_FRUITS      => self::FRUITS,
        self::KEY_GROUP_MEAT        => self::MEAT,
        self::KEY_GROUP_ADDITIONS   => self::ADDITIONS,
        self::KEY_GROUP_DRINKS      => self::DRINKS,
        self::KEY_GROUP_OTHER       => self::OTHER,
    ];

    /**
     * @var array
     */
    public $all = [];

    public function __construct() {

        $this->all = array_merge(
            self::VEGETABLES, self::FRUITS, self::MEAT,
            self::ADDITIONS, self::OTHER, self::DRINKS
        );

    }
}