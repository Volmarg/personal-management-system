<?php
namespace App\DataFixtures\Providers\Modules;

class PaymentsMonthly {

    const MONTHLY_ELECTRICITY       = 'Electricity';
    const MONTHLY_APARTMENT_RENTING = 'Apartment renting';
    const MONTHLY_WATER             = 'Water';
    const MONTHLY_INTERNET          = 'Internet';
    const MONTHLY_MOBILE            = 'Mobile';

    const ALL_MONTHLY = [
      self::MONTHLY_APARTMENT_RENTING   => 500,
      self::MONTHLY_ELECTRICITY         => 30,
      self::MONTHLY_INTERNET            => 18,
      self::MONTHLY_MOBILE              => 20,
      self::MONTHLY_WATER               => 38,
    ];
}