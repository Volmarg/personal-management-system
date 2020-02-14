<?php
namespace App\DataFixtures\Providers\Modules;

class ContactTypes {

    const KEY_NAME        = "name";
    const KEY_IMAGE_PATH  = "image_path";

    const CONTACT_GROUP_FRIENDS  = 'Friends';
    const CONTACT_GROUP_FAMILY   = 'Family';
    const CONTACT_GROUP_WORK     = 'Work';
    const CONTACT_GROUP_OTHER    = 'Other';
    const CONTACT_GROUP_SERVICES = 'Services';

    const ALL_CONTACT_TYPES = [
        [
            self::KEY_NAME        => 'Discord',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/discord.png',
        ],
        [
            self::KEY_NAME        => 'Steam',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/steam.png',
        ],
        [
            self::KEY_NAME        => 'Facebook',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/facebook.png',
        ],
        [
            self::KEY_NAME        => 'Linkedin',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/linkedin.png',
        ],
        [
            self::KEY_NAME        => 'Endomondo',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/endomondo.png',
        ],
        [
            self::KEY_NAME        => 'Instagram',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/instagram.png',
        ],
        [
            self::KEY_NAME        => 'Github',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/github.png',
        ],
        [
            self::KEY_NAME        => 'Email',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/mail.png',
        ],
        [
            self::KEY_NAME        => 'Spotify',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/spotify.png',
        ],
        [
            self::KEY_NAME        => 'Website',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/website.png',
        ],
        [
            self::KEY_NAME        => 'Phone',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/phone.png',
        ],
        [
            self::KEY_NAME        => 'Mobile',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/mobile.png',
        ],
        [
            self::KEY_NAME        => 'Location',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/location.png',
        ],
    ];

    const CONTACT_TYPE_LOCATION = [
        self::KEY_NAME        => 'Location',
        self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/location.png',
    ];

    const CONTACT_TYPE_EMAIL = [
        self::KEY_NAME        => 'Email',
        self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/mail.png',
    ];

    const CONTACT_TYPE_MOBILE = [
        self::KEY_NAME        => 'Mobile',
        self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/mobile.png',
    ];

    const ADDITIONAL_CONTACT_TYPES_EXAMPLES = [
        [
            self::KEY_NAME        => 'Discord',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/discord.png',
        ],
        [
            self::KEY_NAME        => 'Steam',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/steam.png',
        ],
        [
            self::KEY_NAME        => 'Facebook',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/facebook.png',
        ],
        [
            self::KEY_NAME        => 'Linkedin',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/linkedin.png',
        ],
        [
            self::KEY_NAME        => 'Endomondo',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/endomondo.png',
        ],
        [
            self::KEY_NAME        => 'Instagram',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/instagram.png',
        ],
        [
            self::KEY_NAME        => 'Github',
            self::KEY_IMAGE_PATH  => '/upload/images/system/contactIcons/github.png',
        ],
    ];

}