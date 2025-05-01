<?php

namespace App\Services\Validation;

class ValidationService
{
    /**
     * Will validate the provided json string and return bool value:
     * - true if everything is ok
     * - false if something went wrong
     *
     * @param string $json
     * @return bool
     */
    public static function isJsonValid (string $json): bool
    {
        json_decode($json);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return false;
        }

        return true;
    }

}