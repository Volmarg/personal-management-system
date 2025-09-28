<?php

namespace App\Traits;

use Exception;
use Psr\Log\LoggerInterface;
use Throwable;

trait ExceptionLoggerAwareTrait
{
    /**
     * Logs the standard exception data
     *
     * @param Throwable $throwable
     * @param array     $dataBag
     *
     * @throws Exception
     */
    public function logException(Throwable $throwable, array $dataBag = []): void
    {
        if (!isset($this->logger) || !($this->logger instanceof LoggerInterface)) {
            throw new Exception("Logger is not set!");
        }

        $this->logger->critical($throwable->getMessage(), [
            "exceptionCode"  => $throwable->getCode(),
            "exceptionTrace" => $throwable->getTraceAsString(),
            "dataBag"        => $dataBag,
        ]);
    }

}