<?php


namespace App\DataFixtures\Providers;


class FontawesomeIconsProvider
{
    /**
     * Will return random icon string
     *
     * @return string
     */
    public static function getRandomIcon(): string
    {
        $fontawesomeIconsJson  = file_get_contents('./src/assets/scripts/libs/fontawesome-picker/src/iconpicker-1.5.0.json');
        $fontawesomeIconsArray = json_decode($fontawesomeIconsJson,true);
        $randomIcon            = $fontawesomeIconsArray[array_rand($fontawesomeIconsArray)];
        return $randomIcon;
    }

}