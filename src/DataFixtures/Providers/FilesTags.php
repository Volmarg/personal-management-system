<?php
namespace App\DataFixtures\Providers;

class FilesTags {

    const KEY_FILEPATH      = 'filepath';
    const KEY_JSON_TAGS     = 'json_tags';

    const DATA_SETS = [
        [
            self::KEY_FILEPATH => 'upload/files/Documents/Avid_Hardware_Warranty.pdf',
            self::KEY_JSON_TAGS => '["avid","warranty","pdf"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/files/Documents/Templates/Certificate-of-Property-Insurance.zip',
            self::KEY_JSON_TAGS => '["template","certificate","zip","document template","document"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/files/Documents/Templates/residential-lease-template.pdf',
            self::KEY_JSON_TAGS => '["template","apartment rent","home","renting","pdf"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/files/Documents/Templates/Used-Car-Sale-Contract.pdf',
            self::KEY_JSON_TAGS => '["template","car","sell","pdf"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/files/Documents/Manuals/Hp Laserjet cm1015 cmfp.pdf',
            self::KEY_JSON_TAGS => '["manual","hp","laserjet","cm1015"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/files/Documents/Manuals/Thinkpad Edge 13.pdf',
            self::KEY_JSON_TAGS => '["manual","thinkpad","edge13","edge"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/images/Funny/100vuotta.jpg',
            self::KEY_JSON_TAGS => '["funny","images","meme"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/images/Funny/517450_ischab.jpg',
            self::KEY_JSON_TAGS => '["funny","images","meme"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/images/Funny/ahdasta_on4.jpg',
            self::KEY_JSON_TAGS => '["funny","images","meme"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/images/Funny/dEjjHaIFdKk.png',
            self::KEY_JSON_TAGS => '["funny","images","meme"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/images/Wallpapers/Anime/thumb-1920-82029.jpg',
            self::KEY_JSON_TAGS => '["wallpaper","anime"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/images/Wallpapers/Anime/thumb-1920-545909.jpg',
            self::KEY_JSON_TAGS => '["wallpaper","anime"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/images/Wallpapers/Landscapes/61oU8Bu.jpg',
            self::KEY_JSON_TAGS => '["wallpaper","landscape"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/images/Wallpapers/Landscapes/576387.jpg',
            self::KEY_JSON_TAGS => '["wallpaper","landscape"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/images/Wallpapers/Space/earth-3840x2160-stars-hd-5k-3468.jpg',
            self::KEY_JSON_TAGS => '["wallpaper","space"]',
        ],
        [
            self::KEY_FILEPATH => 'upload/images/Wallpapers/Space/pourpre-nebuleuse-eclatant.jpg',
            self::KEY_JSON_TAGS => '["wallpaper","space"]',
        ],
    ];

}