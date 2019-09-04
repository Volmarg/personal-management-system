<?php
namespace App\DataFixtures\Providers\Modules;

use App\DataFixtures\Providers\Providers;

class CarSchedules extends Providers {

    const KEY_INFORMATION   = 'information';
    const KEY_NAME          = 'name';

    CONST OIL_CHANGE = [
      self::KEY_NAME        => 'Oil change',
      self::KEY_INFORMATION => 'Each 100k km',
    ];

    CONST YEARLY_CONTROL = [
        self::KEY_NAME        => 'Oil change',
        self::KEY_INFORMATION => '',
    ];

    CONST INSURANCE = [
        self::KEY_NAME        => 'Insurance',
        self::KEY_INFORMATION => '',
    ];

    CONST TIMING_BELT = [
        self::KEY_NAME        => 'Timing belt',
        self::KEY_INFORMATION => '',
    ];

    CONST WHEELS_CHANGE = [
        self::KEY_NAME        => 'Wheels change',
        self::KEY_INFORMATION => 'Season change',
    ];

    CONST FILTERS_CLEANING = [
        self::KEY_NAME        => 'Filter cleaning',
        self::KEY_INFORMATION => 'Anti fungus',
    ];

    const ALL = [
        self::INSURANCE,
        self::OIL_CHANGE,
        self::TIMING_BELT,
        self::WHEELS_CHANGE,
        self::YEARLY_CONTROL,
        self::FILTERS_CLEANING,
    ];


}