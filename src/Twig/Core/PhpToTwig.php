<?php

namespace App\Twig\Core;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PhpToTwig extends AbstractExtension {

    const DEFAULT_SUBSTRING_LENGTH = 40;

    public function getFunctions() {
        return [
            new TwigFunction('unset', [$this, '_unset']),
            new TwigFunction('unsetKeys', [$this, 'unsetKeys']),
            new TwigFunction('jsonDecode', [$this, 'jsonDecode']),
            new TwigFunction('substring', [$this, 'substring']),
            new TwigFunction('dirname', [$this, 'dirname']),
            new TwigFunction('basename', [$this, 'basename']),
        ];
    }

    /**
     * @param $array
     * @param $key
     * @return mixed
     */
    public function _unset($array, $key) {
        unset($array[$key]);
        return $array;
    }

    /**
     * @param array $array
     * @param array $keys
     * @return array
     */
    public function unsetKeys(array $array, array $keys)
    {
        foreach ($keys as $child) {
            unset($array[$child]);
        }

        return $array;
    }

    /**
     * @param string $json
     * @return array
     */
    public function jsonDecode(string $json): array {
        $arr = json_decode($json, true);
        return $arr;
    }

    /**
     * Extracts substring from string for given offset. Allows to add 3 dots on end of substracted string
     *
     * @param string $string
     * @param int $start
     * @param int $length
     * @param bool $addDots - if true then applies ... on end of string
     * @return string
     */
    public function substring(string $string, int $start = 0, int $length = self::DEFAULT_SUBSTRING_LENGTH, bool $addDots = true): string
    {
        $substring = substr($string, $start, $length);

        if(
                $addDots
            &&  strlen($string) > $length
        ){
            $substring .="...";
        }

        return $substring;
    }

    /**
     * @link https://www.php.net/manual/en/function.dirname.php
     *
     * @param string $directoryPath
     * @return string
     */
    public function dirname(string $directoryPath): string
    {
        return dirname($directoryPath);
    }


    /**
     * @link https://www.php.net/manual/en/function.basename.php
     *
     * @param string $directoryPath
     * @return string
     */
    public function basename(string $directoryPath): string
    {
        return basename($directoryPath);
    }
}