<?php
namespace App\DataFixtures\Providers\Products;

class ExpensiveProducts {

    const PRICE_MIN = 100;
    const PRICE_MAX = 500;

    const GUITAR             = 'guitar';
    const LEATHER_JACKET     = 'leather jacket';
    const FIELD_STOVE        = 'field stove';
    const COMBAT_BOOTS       = 'combat boots';
    const CAR_VACUUM_CLEANER = 'car vacuum cleaner';
    const SMARTPHONE         = 'smartphone';
    const LAPTOP             = 'laptop';
    const GAMING_MOUSE       = 'gaming mouse';

    const ALL = [
      self::GUITAR                  => 'Fender Squier Bullet Strat BK',
      self::LEATHER_JACKET          => "River Island faux leather biker jacket with hood in black",
      self::FIELD_STOVE             => "Lightweight Camping Wood Stoves Compact Kit for Backpacking",
      self::COMBAT_BOOTS            => "Springerstiefel-Para brandit boots",
      self::CAR_VACUUM_CLEANER      => "Xiaomi coclean",
      self::SMARTPHONE              => "Xiaomi Redmi 4A",
      self::LAPTOP                  => "Hp pavilion g7 2310sw",
      self::GAMING_MOUSE            => "Razer Naga"
    ];
}