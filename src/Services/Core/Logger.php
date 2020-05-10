<?php

namespace App\Services\Core;

use Psr\Log\LoggerInterface;

class Logger {

    /**
     * @var LoggerInterface $security_logger
     */
    private $security_logger;

    /**
     * @var LoggerInterface $debug_logger
     */
    private $debug_logger;

    public function __construct(LoggerInterface $security_logger, LoggerInterface $debug_logger)
    {
        $this->security_logger = $security_logger;
        $this->debug_logger    = $debug_logger;
    }

    public function getSecurityLogger(): LoggerInterface
    {
        return $this->security_logger;
    }

    public function getDebugLogger(): LoggerInterface
    {
        return $this->debug_logger;
    }

}