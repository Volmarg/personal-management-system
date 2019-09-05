<?php
namespace App\DataFixtures\Providers\Modules;

class PaymentsSettings {


    const CATEGORY_FOOD                 = 'Food';
    const CATEGORY_DOMESTIC             = 'Domestic';
    const CATEGORY_TRAVELS              = 'Travels';
    const CATEGORY_PERSONAL             = 'Personal';
    const CATEGORY_MONTHLY_PAYMENTS     = 'Monthly payments';

    const CATEGORIES_NAMES = [
        self::CATEGORY_MONTHLY_PAYMENTS,
        self::CATEGORY_PERSONAL,
        self::CATEGORY_DOMESTIC,
        self::CATEGORY_TRAVELS,
        self::CATEGORY_FOOD,
    ];


}