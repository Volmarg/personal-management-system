<?php
namespace App\DataFixtures\Providers\Modules;

class ContactGroups {

    const CONTACT_GROUP_FRIENDS  = 'Friends';
    const CONTACT_GROUP_FAMILY   = 'Family';
    const CONTACT_GROUP_WORK     = 'Work';
    const CONTACT_GROUP_OTHER    = 'Other';
    const CONTACT_GROUP_SERVICES = 'Services';

    const ALL = [
        self::CONTACT_GROUP_FRIENDS,
        self::CONTACT_GROUP_FAMILY,
        self::CONTACT_GROUP_WORK,
        self::CONTACT_GROUP_OTHER,
        self::CONTACT_GROUP_SERVICES,
    ];

}