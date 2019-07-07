<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 08.06.19
 * Time: 08:25
 */

namespace App\Services\Exceptions;


use Throwable;

class ExceptionRepository extends \Exception {

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null) {
        $message = 'Most likely some property, or data in array is missing in general method used for handling repository ajax CRUD.';
        parent::__construct($message, $code, $previous);
    }


}