<?php

namespace App\DTO;

use Exception;

class AbstractDTO{

    /**
     * This function will check if the key in array exists
     * If key exists then it's value will be taken
     * If does not exist - throw exception
     * @param array $array
     * @param string $key
     * @param bool $asString
     * @return string
     * @throws Exception
     */
    public static function checkAndGetKey(array $array, string $key, bool $asString = true) {

        if( array_key_exists($key, $array) ){
            $value = $array[$key];
        }else{
            throw new Exception("{$key} was not found in array made from json in class: " . __CLASS__);
        }

        if( $asString && is_array($value)){
            $value = \GuzzleHttp\json_encode($value);
        }

        return $value;
    }

}