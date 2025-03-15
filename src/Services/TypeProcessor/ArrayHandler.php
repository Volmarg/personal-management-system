<?php

namespace App\Services\TypeProcessor;

use Exception;
use Generator;
use LogicException;

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

    /**
     * Taken from: https://stackoverflow.com/a/17041830
     * Same solution is used on frontend, this is front implementation, if something is going to be changed here, do change
     * it also on frontend.
     *
     * Take the range of characters, and generate an array of all permutations - will not contain the input values
     *
     * @param array       $input                 array used for building product
     * @param array       $result                contains the resulting cartesian product
     *                                           - combination of different values,
     *
     * @param int         $depth                 iterations of product (1 returns array itself, 2 is products made once, 3 twice with array from previous results)
     * @param int         $currentDepth          internal variable to track how nested the current call is
     * @param string      $stringBetween         string added between glued values
     * @param string|null $stringBetweenOriginal same as above (but it's passed around in recursive call, while above one gets modified)
     */
    public static function cartesianProduct(
        array $input,
        array &$result,
        int $depth = 1,
        int $currentDepth = 0,
        string $stringBetween = "",
        ?string $stringBetweenOriginal = null
    ): void
    {
        if (is_null($stringBetweenOriginal)) {
            $stringBetweenOriginal = $stringBetween;
        }

        $excludeSelfDupes = function () use ($stringBetweenOriginal, $input, &$result) {
            foreach ($input as $value) {
                $dupe  = $value . $stringBetweenOriginal . $value;
                $index = array_search($dupe, $result);
                if (is_numeric($index)) {
                    unset($result[$index]);
                }
            }
        };

        $currentDepth++;
        if ($currentDepth > $depth) {
            $excludeSelfDupes();
            return;
        }

        foreach ($input as $char) {
            if (!is_scalar($char)) {
                throw new LogicException("One of the array elements is not scalar. This is not allowed!");
            }

            if ($currentDepth === $depth) {
                $result[] = $stringBetween . $char;
                $excludeSelfDupes();
                continue;
            }

            self:: cartesianProduct($input, $result, $depth, $currentDepth, $char . $stringBetween, $stringBetweenOriginal);
        }
    }
}