<?php

namespace App\Services\Core;

use Psr\Log\LoggerInterface;

class Logger {

    const KEY_MESSAGE     = "message";
    const KEY_ID          = "id";
    const KEY_MODULE_NAME = "module_name";

    /**
     * @var LoggerInterface $securityLogger
     */
    private $securityLogger;

    public function __construct(LoggerInterface $securityLogger)
    {
        $this->securityLogger = $securityLogger;
    }

    public function getSecurityLogger(): LoggerInterface
    {
        return $this->securityLogger;
    }

}