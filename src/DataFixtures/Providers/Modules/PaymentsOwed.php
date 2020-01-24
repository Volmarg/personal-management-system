<?php
namespace App\DataFixtures\Providers\Modules;

use App\DataFixtures\Providers\SettingProvider;

class PaymentsOwed {

    const KEY_TARGET     = 'target';
    const KEY_AMOUNT     = 'amount';
    const KEY_OWED_BY_ME = 'owed_by_me';
    const KEY_INFO       = 'information';
    const KEY_DATE       = 'date';
    const KEY_CURRENCY   = 'currency';

    const OWED_TARGET_ERLEN_SERVICE = 'Erlen Service';
    const OWED_TARGET_TOMASZ        = 'Tomasz Skrzypczyk';
    const OWNED_TARGET_BART         = 'Bart Ridneck';
    const OWNED_TARGET_RORONOA      = 'Roronoa';

    const ALL_OWED_MONEY = [
        [
            self::KEY_TARGET        => self::OWED_TARGET_ERLEN_SERVICE,
            self::KEY_OWED_BY_ME    => true,
            self::KEY_AMOUNT        => 1500,
            self::KEY_INFO          => 'For the laptop',
            self::KEY_DATE          => '2017-05-03',
            self::KEY_CURRENCY      => SettingProvider::KEY_CURRENCY_NAME_PLN,
        ],
        [
            self::KEY_TARGET        => self::OWED_TARGET_TOMASZ,
            self::KEY_OWED_BY_ME    => false,
            self::KEY_AMOUNT        => 150,
            self::KEY_INFO          => 'No idea, he just wanted to borrow',
            self::KEY_DATE          => '2018-05-03',
            self::KEY_CURRENCY      => SettingProvider::KEY_CURRENCY_NAME_PLN,
        ],
        [
            self::KEY_TARGET        => self::OWNED_TARGET_BART,
            self::KEY_OWED_BY_ME    => false,
            self::KEY_AMOUNT        => 60,
            self::KEY_INFO          => "Products that I've bought him on Ebay",
            self::KEY_DATE          => '2018-05-03',
            self::KEY_CURRENCY      => SettingProvider::KEY_CURRENCY_NAME_EUR,
        ],
        [
            self::KEY_TARGET        => self::OWNED_TARGET_RORONOA,
            self::KEY_OWED_BY_ME    => false,
            self::KEY_AMOUNT        => 350,
            self::KEY_INFO          => "Borrowed for mandate on highway",
            self::KEY_DATE          => '2019-02-13',
            self::KEY_CURRENCY      => SettingProvider::KEY_CURRENCY_NAME_CASH,
        ]
    ];
}