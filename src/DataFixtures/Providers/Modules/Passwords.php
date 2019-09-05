<?php
namespace App\DataFixtures\Providers\Modules;

class Passwords {

    const KEY_URL               = 'url';
    const KEY_DESCRIPTION       = 'description';
    const KEY_GROUP             = 'group';

    const PASSWORD_SPARKASSE = [
        self::KEY_URL           => 'https://www.sparkasse.de/',
        self::KEY_DESCRIPTION   => 'German bank account',
        self::KEY_GROUP         => PasswordsGroups::GROUP_FINANCES
    ];

    const PASSWORD_REVOLUT = [
        self::KEY_URL           => 'https://www.revolut.com/',
        self::KEY_DESCRIPTION   => 'International payment account',
        self::KEY_GROUP         => PasswordsGroups::GROUP_FINANCES
    ];

    const PASSWORD_PAYPAL = [
        self::KEY_URL           => 'https://www.paypal.com/',
        self::KEY_DESCRIPTION   => 'My automatic payments and savings',
        self::KEY_GROUP         => PasswordsGroups::GROUP_FINANCES
    ];

    const PASSWORD_FACEBOOK = [
        self::KEY_URL           => 'https://facebook.com',
        self::KEY_DESCRIPTION   => '',
        self::KEY_GROUP         => PasswordsGroups::GROUP_SOCIAL_MEDIA
    ];

    const PASSWORD_INSTAGRAM = [
        self::KEY_URL           => 'https://instagram.com',
        self::KEY_DESCRIPTION   => '',
        self::KEY_GROUP         => PasswordsGroups::GROUP_SOCIAL_MEDIA
    ];

    const PASSWORD_SNAPCHAT = [
        self::KEY_URL           => 'https://www.snapchat.com/',
        self::KEY_DESCRIPTION   => '',
        self::KEY_GROUP         => PasswordsGroups::GROUP_SOCIAL_MEDIA
    ];

    const PASSWORD_GUILD_WARS_2 = [
        self::KEY_URL           => 'https://guildwars2.com',
        self::KEY_DESCRIPTION   => '',
        self::KEY_GROUP         => PasswordsGroups::GROUP_GAMES
    ];

    const PASSWORD_WORLD_OF_WARCRAFT = [
        self::KEY_URL           => 'https://worldofwarcraft.com/en-us/',
        self::KEY_DESCRIPTION   => '',
        self::KEY_GROUP         => PasswordsGroups::GROUP_GAMES
    ];

    const PASSWORD_STEAM = [
        self::KEY_URL           => 'https://store.steampowered.com/',
        self::KEY_DESCRIPTION   => 'Black Desert / Modern Warfare 2',
        self::KEY_GROUP         => PasswordsGroups::GROUP_OTHER
    ];

    const PASSWORD_ORIGIN = [
        self::KEY_URL           => 'https://www.origin.com/',
        self::KEY_DESCRIPTION   => 'For Honor',
        self::KEY_GROUP         => PasswordsGroups::GROUP_OTHER
    ];

    const PASSWORD_REDDIT = [
        self::KEY_URL           => 'https://www.reddit.com/',
        self::KEY_DESCRIPTION   => '',
        self::KEY_GROUP         => PasswordsGroups::GROUP_FORUMS
    ];

    const ALL = [
        self::PASSWORD_SPARKASSE,
        self::PASSWORD_REVOLUT,
        self::PASSWORD_PAYPAL,
        self::PASSWORD_FACEBOOK,
        self::PASSWORD_INSTAGRAM,
        self::PASSWORD_SNAPCHAT,
        self::PASSWORD_GUILD_WARS_2,
        self::PASSWORD_WORLD_OF_WARCRAFT,
        self::PASSWORD_STEAM,
        self::PASSWORD_ORIGIN,
        self::PASSWORD_REDDIT,
    ];

}