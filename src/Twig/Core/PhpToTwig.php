<?php

namespace App\Twig\Core;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PhpToTwig extends AbstractExtension {


    public function getFunctions() {
        return [
            new TwigFunction('unset', [$this, '_unset']),
            new TwigFunction('unsetKeys', [$this, 'unsetKeys']),
            new TwigFunction('jsonDecode', [$this, 'jsonDecode']),
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

}