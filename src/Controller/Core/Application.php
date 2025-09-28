<?php
namespace App\Controller\Core;


use App\Controller\Utils\Utils;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Throwable;

class Application extends AbstractController {

    /**
     * @var LoggerInterface $logger
     */
    public $logger;

    public function __construct(
        LoggerInterface         $logger,
    ) {
        $this->logger        = $logger;
    }

    /**
     * Adds red box message on front
     * @param $message
     */
    public function addDangerFlash($message)
    {
        $this->addFlash(Utils::FLASH_TYPE_DANGER, $message);
    }

    /**
     * Logs the standard exception data
     * @param Throwable $throwable
     * @param array $dataBag
     */
    public function logExceptionWasThrown(Throwable $throwable, array $dataBag = []): void
    {
        $message = $this->translator->translate('messages.general.internalServerError');

        $this->logger->critical($message, [
            "exceptionMessage" => $throwable->getMessage(),
            "exceptionCode"    => $throwable->getCode(),
            "exceptionTrace"   => $throwable->getTraceAsString(),
            "dataBag"          => $dataBag,
        ]);
    }

}