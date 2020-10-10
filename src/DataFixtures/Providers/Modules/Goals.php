<?php
namespace App\DataFixtures\Providers\Modules;


class Goals{

    /**
     * Payment goals
     */
        const GOAL_PAYMENT_LAPTOP           = 'New laptop';
        const GOAL_PAYMENT_HOLIDAY_TRAVEL   = 'Holiday travel';
        const GOAL_PAYMENT_NEW_SWEATERS     = 'New sweaters';
        const GOAL_PAYMENT_SMARTPHONE       = 'Smartphone';
        const GOAL_PAYMENT_FIXING_CAR       = 'Fixing car';
        const GOAL_PAYMENT_BUYING_GUITAR    = 'Guitar';

        const ALL_PAYMENT_GOALS = [
            self::GOAL_PAYMENT_LAPTOP,
            self::GOAL_PAYMENT_HOLIDAY_TRAVEL,
            self::GOAL_PAYMENT_NEW_SWEATERS,
            self::GOAL_PAYMENT_SMARTPHONE,
            self::GOAL_PAYMENT_FIXING_CAR,
            self::GOAL_PAYMENT_BUYING_GUITAR,
        ];

    /**
     * all
     */
        const KEY_GROUP_PAYMENTS_GOALS  = 'payments_goals';
        const ALL = [
            self::KEY_GROUP_PAYMENTS_GOALS  => self::ALL_PAYMENT_GOALS,
        ];
}