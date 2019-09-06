<?php
namespace App\DataFixtures\Providers\Modules;

class Achievements {

    #soft
    const SOFT_FAVOURITE_BAND_CONCERT   = 'Participating in favourite band concert';
    const SOFT_LEARNING_LINUX           = 'Learning linux';
    const SOFT_POLAND_ROCK_FESTIVAL     = 'Participating in poland rock festival';

    const SOFT = [
        self::SOFT_LEARNING_LINUX,
        self::SOFT_POLAND_ROCK_FESTIVAL,
        self::SOFT_FAVOURITE_BAND_CONCERT,
    ];

    #mediium
    const MEDIUM_LEARNING_PHP           = 'Learning PHP';
    const MEDIUM_LEARNING_JS            = 'Learning JS';
    const MEDIUM_LEARNING_SYMFONY       = 'Learning Symfony';
    const MEDIUM_LEARNING_CODE_QUALITY  = 'Learning code quality';
    const MEDIUM_FINISHING_UNIVERSITY   = 'Finishing university';

    const MEDIUM = [
        self::MEDIUM_LEARNING_JS,
        self::MEDIUM_LEARNING_PHP,
        self::MEDIUM_LEARNING_SYMFONY,
        self::MEDIUM_FINISHING_UNIVERSITY,
        self::MEDIUM_LEARNING_CODE_QUALITY,
    ];

    #hard
    const HARD_FINISHING_PERSONAL_PROJECT_V_1_0  = 'Finishing personal project 1.0';
    const HARD_LEARN_GERMAN_VERY_BASIC           = 'Learn german very basic';
    const HARD_LOOSING_20KG_WEIGHT               = 'Loosing 20kg weight';
    const HARD_LEARNING_MARTIAL_ARTS             = 'Learning martial arts';

    const HARD = [
        self::HARDCORE_WORK_IN_FOREIGN_COUNTRY,
        self::HARDCORE_START_LIVING_ON_YOUR_OWN,
    ];

    #hardcore
    const HARDCORE_WORK_IN_FOREIGN_COUNTRY       = 'Work in foreign country';
    const HARDCORE_START_LIVING_ON_YOUR_OWN      = 'Start living on your own';

    const HARDCORE = [
        self::HARD_LOOSING_20KG_WEIGHT,
        self::HARD_LEARNING_MARTIAL_ARTS,
        self::HARD_LEARN_GERMAN_VERY_BASIC,
        self::HARD_FINISHING_PERSONAL_PROJECT_V_1_0,
    ];

    #all
    const KEY_GROUP_SIMPLE   = 'simple';
    const KEY_GROUP_MEDIUM   = 'medium';
    const KEY_GROUP_HARD     = 'hard';
    const KEY_GROUP_HARDCORE = 'hardcore';

    const ALL = [
        self::KEY_GROUP_SIMPLE      => self::SOFT,
        self::KEY_GROUP_MEDIUM      => self::MEDIUM,
        self::KEY_GROUP_HARD        => self::HARD,
        self::KEY_GROUP_HARDCORE    => self::HARDCORE,

    ];

    /**
     * @var boolean $areGroups
     */
    private $areGroups = true;

}