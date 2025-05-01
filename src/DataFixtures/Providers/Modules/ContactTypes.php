<?php
namespace App\DataFixtures\Providers\Modules;

class ContactTypes {

    private const string DISCORD = 'https://cdn0.iconfinder.com/data/icons/free-social-media-set/24/discord-256.png';
    private const string STEAM = 'https://cdn3.iconfinder.com/data/icons/social-media-2169/24/social_media_social_media_logo_steam-256.png';
    private const string FACEBOOK = 'https://cdn2.iconfinder.com/data/icons/social-media-2285/512/1_Facebook_colored_svg_copy-256.png';
    private const string LINKEDIN = 'https://cdn2.iconfinder.com/data/icons/social-media-2285/512/1_Linkedin_unofficial_colored_svg-256.png';
    private const string INSTAGRAM = 'https://cdn2.iconfinder.com/data/icons/social-icons-33/128/Instagram-256.png';
    private const string GITHUB = 'https://cdn1.iconfinder.com/data/icons/picons-social/57/github_rounded-256.png';
    private const string EMAIL = 'https://cdn3.iconfinder.com/data/icons/colorful-guache-social-media-logos-1/154/social-media_email_new-3-256.png';
    private const string SPOTIFY = 'https://cdn2.iconfinder.com/data/icons/social-icons-33/128/Spotify-256.png';
    private const string WEBSITE = 'https://cdn1.iconfinder.com/data/icons/social-media-logos-7/64/chrome-256.png';
    private const string PHONE = 'https://cdn4.iconfinder.com/data/icons/social-media-2097/94/phone-256.png';
    private const string MOBILE = 'https://cdn0.iconfinder.com/data/icons/social-media-2291/60/20-256.png';
    private const string LOCATION = 'https://cdn2.iconfinder.com/data/icons/social-media-2259/512/google-256.png';


    const KEY_NAME        = "name";
    const KEY_IMAGE_PATH  = "image_path";

    const ALL_CONTACT_TYPES = [
        [
            self::KEY_NAME        => 'Discord',
            self::KEY_IMAGE_PATH  => self::DISCORD,
        ],
        [
            self::KEY_NAME        => 'Steam',
            self::KEY_IMAGE_PATH  => self::STEAM,
        ],
        [
            self::KEY_NAME        => 'Facebook',
            self::KEY_IMAGE_PATH  => self::FACEBOOK,
        ],
        [
            self::KEY_NAME        => 'Linkedin',
            self::KEY_IMAGE_PATH  => self::LINKEDIN,
        ],
        [
            self::KEY_NAME        => 'Instagram',
            self::KEY_IMAGE_PATH  => self::INSTAGRAM,
        ],
        [
            self::KEY_NAME        => 'Github',
            self::KEY_IMAGE_PATH  => self::GITHUB,
        ],
        [
            self::KEY_NAME        => 'Email',
            self::KEY_IMAGE_PATH  => self::EMAIL,
        ],
        [
            self::KEY_NAME        => 'Spotify',
            self::KEY_IMAGE_PATH  => self::SPOTIFY,
        ],
        [
            self::KEY_NAME        => 'Website',
            self::KEY_IMAGE_PATH  => self::WEBSITE,
        ],
        [
            self::KEY_NAME        => 'Phone',
            self::KEY_IMAGE_PATH  => self::PHONE,
        ],
        [
            self::KEY_NAME        => 'Mobile',
            self::KEY_IMAGE_PATH  => self::MOBILE,
        ],
        [
            self::KEY_NAME        => 'Location',
            self::KEY_IMAGE_PATH  => self::LOCATION,
        ],
    ];

    const CONTACT_TYPE_LOCATION = [
        self::KEY_NAME        => 'Location',
        self::KEY_IMAGE_PATH  => self::LOCATION,
    ];

    const CONTACT_TYPE_EMAIL = [
        self::KEY_NAME        => 'Email',
        self::KEY_IMAGE_PATH  => self::EMAIL,
    ];

    const CONTACT_TYPE_MOBILE = [
        self::KEY_NAME        => 'Mobile',
        self::KEY_IMAGE_PATH  => self::MOBILE,
    ];

    const ADDITIONAL_CONTACT_TYPES_EXAMPLES = [
        [
            self::KEY_NAME        => 'Discord',
            self::KEY_IMAGE_PATH  => self::DISCORD,
        ],
        [
            self::KEY_NAME        => 'Steam',
            self::KEY_IMAGE_PATH  => self::STEAM,
        ],
        [
            self::KEY_NAME        => 'Facebook',
            self::KEY_IMAGE_PATH  => self::FACEBOOK,
        ],
        [
            self::KEY_NAME        => 'Linkedin',
            self::KEY_IMAGE_PATH  => self::LINKEDIN,
        ],
        [
            self::KEY_NAME        => 'Instagram',
            self::KEY_IMAGE_PATH  => self::INSTAGRAM,
        ],
        [
            self::KEY_NAME        => 'Github',
            self::KEY_IMAGE_PATH  => self::GITHUB,
        ],
    ];

}