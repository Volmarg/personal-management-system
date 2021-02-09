<?php

namespace App\Form\Events;


class Utils {

    private static $ucProperty;
    private static $lcProperty;

    /**
     * @param $dataElementsToModify
     * @param $modifiedData
     * @return array
     * @throws \Exception
     */
    public static function modifyEventData($dataElementsToModify, $modifiedData) {

        // FIX: for exceeded uploaded file size - $_FILES is empty, and symfony does not provide any errors
        if( is_null($dataElementsToModify) ){
            return [];
        }

        foreach ($dataElementsToModify as $modifiedProperty => $newValue) {
            static::$ucProperty = ucfirst($modifiedProperty);
            static::$lcProperty = lcfirst($modifiedProperty);

            if (is_object($modifiedData)) {
                $modifiedData = static::modifyEventDataForObject($modifiedData, $newValue);
            } else {
                $modifiedData = static::modifyEventDataForArray($modifiedData, $newValue);
            }
        }

        return $modifiedData;
    }


    /**
     * @param $modifiedData
     * @param $newValue
     * @return mixed
     * @throws \Exception
     */
    private static function modifyEventDataForObject($modifiedData, $newValue) {

        if (property_exists($modifiedData, static::$ucProperty)) {
            $modifiedData->{'set' . static::$ucProperty}($newValue);
        } elseif (property_exists($modifiedData, static::$lcProperty)) {
            $modifiedData->{'set' . static::$lcProperty}($newValue);
        } else {
            static::throwExceptionMissingProperty([static::$ucProperty, static::$lcProperty]);
        }

        return $modifiedData;
    }

    /**
     * @param $modifiedData
     * @param $newValue
     * @return mixed
     */
    private static function modifyEventDataForArray($modifiedData, $newValue) {

        // FIX: for exceeded uploaded file size - $_FILES is empty, and symfony does not provide any errors
        if( is_null($modifiedData) ){
            return [];
        }

        if (array_key_exists(static::$ucProperty, $modifiedData)) {
            $modifiedData[static::$ucProperty] = $newValue;
        } elseif (array_key_exists(static::$lcProperty, $modifiedData)) {
            $modifiedData[static::$lcProperty] = $newValue;
        } else {
            $modifiedData[static::$lcProperty] = $newValue;
        }

        return $modifiedData;
    }

    /**
     * @param array $properties
     * @throws \Exception
     */
    private static function throwExceptionMissingProperty(array $properties) {
        $propertiesString = json_encode($properties);
        throw new \Exception("For some reason the object is missing the property that You try to modify ({$propertiesString})");
    }

}