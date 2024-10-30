<?php

namespace App\DTO;

use Exception;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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

    /**
     * Takes the json and deserializes it into calling class dto
     *
     * @param string $json
     *
     * @return static
     */
    public static function deserialize(string $json): static
    {
        return self::getSerializer()->deserialize($json, static::class, "json");
    }

    /**
     * Turns current calling class into json
     *
     * @return string
     */
    public function serialize(): string
    {
        return self::getSerializer()->serialize($this, "json");
    }

    /**
     * Serializer used in context of dto
     *
     * @return Serializer
     */
    private static function getSerializer(): Serializer
    {
        return new Serializer([
            new ObjectNormalizer(),
        ], [
            new JsonEncoder(),
        ]);
    }

}