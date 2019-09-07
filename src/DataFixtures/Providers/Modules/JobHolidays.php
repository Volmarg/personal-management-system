<?php
namespace App\DataFixtures\Providers\Modules;

class JobHolidays{

    const KEY_YEAR              = 'year';
    const KEY_HOLIDAYS_COUNT    = 'holidays_count';

    const HOLIDAY_COMPANY_NAME_POTATO_AND_BEANS  = 'Potato & Beans';
    const HOLIDAY_COMPANY_NAME_STEPHEN_CLING     = 'Stephen Cling';
    const HOLIDAY_COMPANY_NAME_FEST_SERVICE      = 'Fest Service';

    const HOLIDAY_REASON_FRIENDS = "Visiting friends";
    const HOLIDAY_REASON_TRAVEL  = "Travel";
    const HOLIDAY_REASON_CAR_FIX = "Fixing car";

    const ALL_COMPANIES = [
        self::HOLIDAY_COMPANY_NAME_POTATO_AND_BEANS => [
            self::KEY_YEAR              => 2017,
            self::KEY_HOLIDAYS_COUNT    => 30,
        ],
        self::HOLIDAY_COMPANY_NAME_STEPHEN_CLING => [
            self::KEY_YEAR              => 2018,
            self::KEY_HOLIDAYS_COUNT    => 28,
        ],
        self::HOLIDAY_COMPANY_NAME_FEST_SERVICE => [
            self::KEY_YEAR              => 2019,
            self::KEY_HOLIDAYS_COUNT    => 22,
        ],
    ];

    const ALL_REASONS = [
        self::HOLIDAY_REASON_FRIENDS,
        self::HOLIDAY_REASON_TRAVEL,
        self::HOLIDAY_REASON_CAR_FIX,
    ];

}