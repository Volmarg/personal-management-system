<?php

namespace App\VO;

abstract class AbstractVO {

    /**
     * This function will check if the key in array exists
     * If key exists then it's value will be taken
     * If does not exist - throw exception
     * @param array $array
     * @param string $key
     * @param $defaultValue
     * @return mixed
     */
    public static function checkAndGetKey(array $array, string $key, $defaultValue) {

        if( array_key_exists($key, $array) ){
            return $array[$key];
        }else{
            return $defaultValue;
        }
    }

}