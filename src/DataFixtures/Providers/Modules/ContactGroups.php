<?php
namespace App\DataFixtures\Providers\Modules;

class ContactGroups {

    const KEY_NAME  = "name";
    const KEY_ICON  = "icon";
    const KEY_COLOR = "color";

    const CONTACT_GROUP_FAMILY   = 'Family';
    const CONTACT_GROUP_OTHER    = 'Other';
    const CONTACT_GROUP_SERVICES = 'Services';

    const ALL_CONTACT_GROUPS = [
        [
            self::KEY_NAME  => 'Medic',
            self::KEY_ICON  => 'fas fa-briefcase-medical',
            self::KEY_COLOR => '399e05',
        ],
        [
            self::KEY_NAME  => 'Work',
            self::KEY_ICON  => 'fas fa-suitcase',
            self::KEY_COLOR => 'be5e05',
        ],
        [
            self::KEY_NAME  => 'Service',
            self::KEY_ICON  => 'fas fa-cog',
            self::KEY_COLOR => '3f3b3b',
        ],
        [
            self::KEY_NAME  => 'Family',
            self::KEY_ICON  => 'fas fa-home',
            self::KEY_COLOR => '276ad7',
        ],
        [
            self::KEY_NAME  => 'Friend',
            self::KEY_ICON  => 'fas fa-male',
            self::KEY_COLOR => 'cd2ecc',
        ],
        [
            self::KEY_NAME  => 'Vip',
            self::KEY_ICON  => 'far fa-star',
            self::KEY_COLOR => 'ffd000',
        ],
        [
            self::KEY_NAME  => 'Game fellow',
            self::KEY_ICON  => 'fas fa-gamepad',
            self::KEY_COLOR => 'f23e4e',
        ],
        [
            self::KEY_NAME  => 'Archived',
            self::KEY_ICON  => 'far fa-times-circle',
            self::KEY_COLOR => 'fb5705',
        ]
    ];

}