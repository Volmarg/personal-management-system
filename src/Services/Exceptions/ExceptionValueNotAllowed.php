<?php
namespace App\Services\Exceptions;
use Throwable;

class ExceptionValueNotAllowed extends \Exception {

    const KEY_MODE_NUMERIC_NOT_HIGHER_THAN_0 = "KEY_MODE_NUMERIC_NOT_HIGHER_THAN_0";
    const KEY_MODE_STRING_NOT_EMPTY          = "KEY_MODE_STRING_NOT_EMPTY";

    public function __construct($mode, $value = '') {

        switch( $mode ){
            case self::KEY_MODE_NUMERIC_NOT_HIGHER_THAN_0:
                $message = "Expected value higher than 0, got: " . $value;
                break;
            case self::KEY_MODE_STRING_NOT_EMPTY:
                $message = "Got empty string - expected non empty value.";
                break;
            default:
                $message = "Unknown mode: " . $mode;
        }
        parent::__construct($message);
    }


}