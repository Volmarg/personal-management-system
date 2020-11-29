<?php

namespace App\Form\Events;


class Utils {

    private static $uc_property;
    private static $lc_property;

    /**
     * @param $data_elements_to_modify
     * @param $modified_data
     * @return array
     * @throws \Exception
     */
    public static function modifyEventData($data_elements_to_modify, $modified_data) {

        // FIX: for exceeded uploaded file size - $_FILES is empty, and symfony does not provide any errors
        if( is_null($data_elements_to_modify) ){
            return [];
        }

        foreach ($data_elements_to_modify as $modified_property => $new_value) {
            static::$uc_property = ucfirst($modified_property);
            static::$lc_property = lcfirst($modified_property);

            if (is_object($modified_data)) {
                $modified_data = static::modifyEventDataForObject($modified_data, $new_value);
            } else {
                $modified_data = static::modifyEventDataForArray($modified_data, $new_value);
            }
        }

        return $modified_data;
    }


    /**
     * @param $modified_data
     * @param $new_value
     * @return mixed
     * @throws \Exception
     */
    private static function modifyEventDataForObject($modified_data, $new_value) {

        if (property_exists($modified_data, static::$uc_property)) {
            $modified_data->{'set' . static::$uc_property}($new_value);
        } elseif (property_exists($modified_data, static::$lc_property)) {
            $modified_data->{'set' . static::$lc_property}($new_value);
        } else {
            static::throwExceptionMissingProperty([static::$uc_property, static::$lc_property]);
        }

        return $modified_data;
    }

    /**
     * @param $modified_data
     * @param $new_value
     * @return mixed
     */
    private static function modifyEventDataForArray($modified_data, $new_value) {

        // FIX: for exceeded uploaded file size - $_FILES is empty, and symfony does not provide any errors
        if( is_null($modified_data) ){
            return [];
        }

        if (array_key_exists(static::$uc_property, $modified_data)) {
            $modified_data[static::$uc_property] = $new_value;
        } elseif (array_key_exists(static::$lc_property, $modified_data)) {
            $modified_data[static::$lc_property] = $new_value;
        } else {
            $modified_data[static::$lc_property] = $new_value;
        }

        return $modified_data;
    }

    /**
     * @param array $properties
     * @throws \Exception
     */
    private static function throwExceptionMissingProperty(array $properties) {
        $properties_string = json_encode($properties);
        throw new \Exception("For some reason the object is missing the property that You try to modify ({$properties_string})");
    }

}