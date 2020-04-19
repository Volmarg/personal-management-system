<?php
namespace App\DataFixtures\Providers\Modules;

class Schedules {

    const KEY_NAME              = 'name';
    const KEY_ICON              = 'icon';
    const KY_SCHEDULE_TYPE_NAME = 'schedule_type_name';
    const KEY_IS_DATE_BASED     = "is_date_based";
    const KEY_INFORMATION       = "information";

    const ALL_CAR_SCHEDULES = [
        [
            self::KY_SCHEDULE_TYPE_NAME => 'car',
            self::KEY_NAME              => 'Insurance',
            self::KEY_IS_DATE_BASED     => true,
            self::KEY_INFORMATION       => '',
        ],
        [
            self::KY_SCHEDULE_TYPE_NAME => 'car',
            self::KEY_NAME              => 'Oil change',
            self::KEY_IS_DATE_BASED     => true,
            self::KEY_INFORMATION       => 'Each 10k km',
        ],
        [
            self::KY_SCHEDULE_TYPE_NAME => 'car',
            self::KEY_NAME              => 'Timing belt',
            self::KEY_IS_DATE_BASED     => true,
            self::KEY_INFORMATION       => '',
        ],
        [
            self::KY_SCHEDULE_TYPE_NAME => 'car',
            self::KEY_NAME              => 'Wheels change',
            self::KEY_IS_DATE_BASED     => true,
            self::KEY_INFORMATION       => '',
        ],
        [
            self::KY_SCHEDULE_TYPE_NAME => 'car',
            self::KEY_NAME              => 'Insurance',
            self::KEY_IS_DATE_BASED     => true,
            self::KEY_INFORMATION       => 'Season change',
        ],
        [
            self::KY_SCHEDULE_TYPE_NAME => 'car',
            self::KEY_NAME              => 'Filter cleaning',
            self::KEY_IS_DATE_BASED     => true,
            self::KEY_INFORMATION       => 'Anti fungus',
        ],
    ];

    const ALL_HOME_SCHEDULES = [
        [
            self::KY_SCHEDULE_TYPE_NAME => 'home',
            self::KEY_NAME              => 'Painting wall in kitchen',
            self::KEY_IS_DATE_BASED     => true,
            self::KEY_INFORMATION       => '',
        ],
        [
            self::KY_SCHEDULE_TYPE_NAME => 'home',
            self::KEY_NAME              => 'Repair table',
            self::KEY_IS_DATE_BASED     => true,
            self::KEY_INFORMATION       => 'Each 10k km',
        ],
    ];

    const ALL_SCHEDULES_TYPES = [
        [
            self::KEY_NAME  => 'car',
            self::KEY_ICON  => 'fas fa-car-alt',
        ],
        [
            self::KEY_NAME  => 'home',
            self::KEY_ICON  => 'fas fa-home',
        ],
    ];

}