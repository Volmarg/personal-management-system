<?php
namespace App\DataFixtures\Providers\Modules;

class PasswordsGroups {

    /* Goals */
    const GROUP_FINANCES        = 'finances';
    const GROUP_GAMES           = 'games';
    const GROUP_FORUMS          = 'forums';
    const GROUP_SOCIAL_MEDIA    = 'social media';
    const GROUP_OTHER           = 'other';

    const ALL = [
        self::GROUP_FINANCES,
        self::GROUP_GAMES,
        self::GROUP_FORUMS,
        self::GROUP_SOCIAL_MEDIA,
        self::GROUP_OTHER,
    ];

}