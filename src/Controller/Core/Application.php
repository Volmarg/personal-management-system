<?php
namespace App\Controller\Core;


use App\Controller\Utils\Utils;
use App\Services\Core\Translator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class Application extends AbstractController {

    /**
     * @var \App\Services\Core\Translator $translator
     */
    public $translator;

    /**
     * @var LoggerInterface $logger
     */
    public $logger;

    public function __construct(
        LoggerInterface         $logger,
        TranslatorInterface     $translator,
    ) {
        $this->logger        = $logger;
        $this->translator    = new Translator($translator);
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