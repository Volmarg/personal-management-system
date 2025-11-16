<?php

namespace App\Services;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Utils extends AbstractController {

    const FLASH_TYPE_DANGER  = "danger";

    const TRUE_AS_STRING  = "true";
    const FALSE_AS_STRING = "false";

    /**
     * @param string $dir
     * @return bool
     */
    public static function removeFolderRecursively(string $dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? static::removeFolderRecursively("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * Get one random element from array
     * @param array $array
     * @return mixed
     */
    public static function arrayGetRandom(array $array) {
        $index      = array_rand($array);
        $element    = $array[$index];

        return $element;
    }

    /**
     * @param array $data
     * @param int $count
     * @return array
     */
    public static function arrayGetNotRepeatingValuesCount(array $data, int $count) {

        $randoms = [];

        for($x = 0; $x <= $count; $x++) {

            $arrayIndex = array_rand($data);
            $randoms[]  = $data[$arrayIndex];

            unset($data[$arrayIndex]);

            if( empty ($data) ) {
                break;
            }

        }

        return $randoms;
    }

    /**
     * @param string|bool $value
     * @return bool
     * @throws Exception
     */
    public static function getBoolRepresentationOfBoolString($value): bool
    {
        if( is_bool($value) ){
            return $value;
        }elseif( is_string($value) ){

            $allowedValues = [
                self::TRUE_AS_STRING, self::FALSE_AS_STRING
            ];

            if( !in_array($value, $allowedValues) ){
                throw new Exception("Not a bool string");
            }

            return self::TRUE_AS_STRING === $value;
        }else{
            throw new \TypeError("Not allowed type: " . gettype($value) );
        }

    }

    /**
     * Will turn the string version of array into real array. The required syntax is:
     * [\"127.0.0.1\", \"192.168.10.1\"]
     *
     * @param string $stringArray
     * @return array
     * @throws Exception
     */
    public static function getRealArrayForStringArray(string $stringArray): array
    {
        $realArray = json_decode($stringArray);
        if( empty($realArray) ){
            return [];
        }

        if(
            JSON_ERROR_NONE != json_last_error()
            ||  (
                    !empty($stringArray)
                &&  "[]" !== $stringArray
                &&  empty($realArray)
            )
        ){
            throw new Exception("Incorrect syntax of array");
        }

        return $realArray;
    }
}
