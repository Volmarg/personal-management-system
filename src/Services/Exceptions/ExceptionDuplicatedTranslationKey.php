<?php

namespace App\Services\Exceptions;

use Throwable;

class ExceptionDuplicatedTranslationKey extends \Exception {

    /**
     * ExceptionDuplicatedTranslationKey constructor.
     * @param string $key
     * @param string $found_in_file
     * @param string $duplicate_found_in_file
     */
    public function __construct(string $key, string $found_in_file, string $duplicate_found_in_file) {
        $message = "There is duplicated key ({$key}) in Your translation files! Translation was first found in: '{$found_in_file}' and then in '{$duplicate_found_in_file}'.";
        parent::__construct($message, 500, null);
    }


}