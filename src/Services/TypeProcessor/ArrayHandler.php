<?php

namespace App\Services\TypeProcessor;

use Exception;

/**
 * Contains logic for handling the arrays
 */
class ArrayHandler
{
    /**
     * This function will check if the key in array exists
     * If key exists then it's value will be taken
     * If does not exist - returns default value
     *
     * @param array $array
     * @param string $key
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public static function checkAndGetKey(array $array, string $key, mixed $defaultValue = null): mixed
    {
        if( array_key_exists($key, $array) ){
            $value = $array[$key];
            return $value;
        }

        return $defaultValue;
    }

    /**
     * Returns value under given key in source array.
     * Throws exception, ff target key does not exist in the source.
     *
     * @throws Exception
     */
    public static function get(array $source, string $key, bool $allowDefault = false, mixed $default = null): mixed
    {
        if (!array_key_exists($key, $source)) {
            if ($allowDefault) {
                return $default;
            }

            throw new Exception("Key {$key} not found in the array");
        }

        return $source[$key];
    }

}