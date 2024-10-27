<?php

namespace App\Services\Core;

use Psr\Log\LoggerInterface;
use Throwable;

class Logger {

    const KEY_MESSAGE     = "message";
    const KEY_ID          = "id";
    const KEY_MODULE_NAME = "module_name";

    /**
     * @var LoggerInterface $securityLogger
     */
    private $securityLogger;

    public function __construct(
        LoggerInterface $securityLogger,
        private readonly LoggerInterface $logger,
    )
    {
        $this->securityLogger = $securityLogger;
    }

    public function getSecurityLogger(): LoggerInterface
    {
        return $this->securityLogger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Will log the exception
     *
     * @param Throwable $e
     * @param array     $additionalData
     */
    public function logException(Throwable $e, array $additionalData = []): void
    {
        $this->logger->critical("Exception was thrown", [
            "exceptionClass"   => get_class($e),
            "exceptionMessage" => $e->getMessage(),
            "exceptionCode"    => $e->getCode(),
            "exceptionTrace"   => $e->getTrace(),
            "additionalData"   => $additionalData,
        ]);
    }


}