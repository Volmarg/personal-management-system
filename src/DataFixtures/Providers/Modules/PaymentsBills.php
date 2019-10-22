<?php
namespace App\DataFixtures\Providers\Modules;

class PaymentsBills {

    // BILLS
    const KEY_BILL_START_DATE       = 'bill_start_date';
    const KEY_BILL_END_DATE         = 'bill_end_date';
    const KEY_BILL_NAME             = 'bill_name';
    const KEY_BILL_INFORMATION      = 'bill_information';
    const KEY_BILL_PLANNED_AMOUNT   = 'bill_planned_amount';
    const KEY_BILL_ID               = 'bill_id';

    const ALL_BILLS = [
        [
            self::KEY_BILL_ID               => 1,
            self::KEY_BILL_NAME             => 'Summer holidays',
            self::KEY_BILL_INFORMATION      => 'At sea',
            self::KEY_BILL_START_DATE       => '2019-09-03',
            self::KEY_BILL_END_DATE         => '2019-09-20',
            self::KEY_BILL_PLANNED_AMOUNT   => '900',
        ],
        [
            self::KEY_BILL_ID               => 2,
            self::KEY_BILL_NAME             => 'Car repair',
            self::KEY_BILL_INFORMATION      => 'Broke on way back from work',
            self::KEY_BILL_START_DATE       => '2019-10-03',
            self::KEY_BILL_END_DATE         => '2019-10-05',
            self::KEY_BILL_PLANNED_AMOUNT   => '400',
        ],
        [
            self::KEY_BILL_ID               => 3,
            self::KEY_BILL_NAME             => 'Concert',
            self::KEY_BILL_INFORMATION      => 'During summerfest',
            self::KEY_BILL_START_DATE       => '2019-08-03',
            self::KEY_BILL_END_DATE         => '2019-08-03',
            self::KEY_BILL_PLANNED_AMOUNT   => '500',
        ],
    ];

    // BILL ITEMS
    const KEY_BILL_ITEM_BILL_NAME   = 'bill_name';
    const KEY_BILL_ITEM_AMOUNT      = 'amount';
    const KEY_BILL_ITEM_NAME        = 'name';
    const KEY_BILL_ITEM_DATE        = 'date';

    const ALL_BILLS_ITEMS = [
        [
            self::KEY_BILL_ITEM_NAME        => 'Travel',
            self::KEY_BILL_ITEM_BILL_NAME   => 'Summer holidays',
            self::KEY_BILL_ITEM_AMOUNT      => '150',
            self::KEY_BILL_ITEM_DATE        => '2019-09-03',
        ],
        [
            self::KEY_BILL_ITEM_NAME        => 'Hotel',
            self::KEY_BILL_ITEM_BILL_NAME   => 'Summer holidays',
            self::KEY_BILL_ITEM_AMOUNT      => '350',
            self::KEY_BILL_ITEM_DATE        => '2019-09-03',
        ],
        [
            self::KEY_BILL_ITEM_NAME        => 'Food',
            self::KEY_BILL_ITEM_BILL_NAME   => 'Summer holidays',
            self::KEY_BILL_ITEM_AMOUNT      => '400',
            self::KEY_BILL_ITEM_DATE        => '2019-09-03',
        ],
        [
            self::KEY_BILL_ITEM_NAME        => 'New spare parts',
            self::KEY_BILL_ITEM_BILL_NAME   => 'Car repair',
            self::KEY_BILL_ITEM_AMOUNT      => '400',
            self::KEY_BILL_ITEM_DATE        => '2019-10-03',
        ],
        [
            self::KEY_BILL_ITEM_NAME        => 'Work payment',
            self::KEY_BILL_ITEM_BILL_NAME   => 'Car repair',
            self::KEY_BILL_ITEM_AMOUNT      => '100',
            self::KEY_BILL_ITEM_DATE        => '2019-10-05',
        ],
        [
            self::KEY_BILL_ITEM_NAME        => 'Ticket',
            self::KEY_BILL_ITEM_BILL_NAME   => 'Concert',
            self::KEY_BILL_ITEM_AMOUNT      => '250',
            self::KEY_BILL_ITEM_DATE        => '2019-08-03',
        ],
        [
            self::KEY_BILL_ITEM_NAME        => 'Hotel',
            self::KEY_BILL_ITEM_BILL_NAME   => 'Concert',
            self::KEY_BILL_ITEM_AMOUNT      => '100',
            self::KEY_BILL_ITEM_DATE        => '2019-08-03',
        ],
        [
            self::KEY_BILL_ITEM_NAME        => 'Booze and food',
            self::KEY_BILL_ITEM_BILL_NAME   => 'Concert',
            self::KEY_BILL_ITEM_AMOUNT      => '300',
            self::KEY_BILL_ITEM_DATE        => '2019-08-03',
        ]
    ];

}