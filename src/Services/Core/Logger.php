<?php

namespace App\Services\Core;

use Psr\Log\LoggerInterface;

class Logger {

    /**
     * @var LoggerInterface $security_logger
     */
    private $security_logger;

    public function __construct(LoggerInterface $security_logger)
    {
        $this->security_logger = $security_logger;
    }

    public function getSecurityLogger(): LoggerInterface
    {
        return $this->security_logger;
    }

}