<?php

namespace App\Exception;

use Exception;

class MissingDataException extends Exception
{
    private $front = true;

    public function isFront(): bool
    {
        return $this->front;
    }

    public function setFront(bool $isRequest): void
    {
        $this->front = $isRequest;
    }

}