<?php


namespace App\Controller\Utils;

/**
 * Utils for arrays
 *
 * Class ArrayUtils
 * @package App\Controller\Utils
 */
class ArrayUtils
{

    /**
     * Will create multidimensional array from string which contains the same separator (like for example json/yaml)
     *
     * @link https://stackoverflow.com/questions/37356391/php-explode-string-key-into-multidimensional-array-with-values/37356543
     *
     * @param string $delimiter
     * @param string $string
     * @param string $valueForLastElement
     * @return array
     */
    public static function stringIntoMultidimensionalArray(string $delimiter, string $string, string $valueForLastElement = ""): array
    {
        $result         = [];
        $temp           =& $result;
        $arrayOfStrings = explode($delimiter, $string);

        foreach( $arrayOfStrings as $index => $key) {

            if( $index === count($arrayOfStrings) -1 ){

                $temp[$key] = $valueForLastElement;
            }else{

                $temp =& $temp[$key]; // no idea what this does in the end, but works...
            }

        }

        return $result;
    }

}