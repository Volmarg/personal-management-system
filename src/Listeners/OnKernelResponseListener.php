<?php

namespace App\Listeners;

use App\Services\Logger;
use App\Services\Translator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Removing unwanted headers - this way it's not depending on server config
 * Class OnKernelResponseListener
 * @package App\Listeners
 */
class OnKernelResponseListener implements EventSubscriberInterface {

    /**
     * @var Logger $security_logger
     */
    private $security_logger;

    /**
     * @var Translator $translator
     */
    private $translator;

    public function __construct(Logger $security_logger, Translator $translator) {
        $this->security_logger = $security_logger->getSecurityLogger();
        $this->translator      = $translator;
    }

    /**
     * @param ResponseEvent $ev
     */
    public function oneResponse(ResponseEvent $ev)
    {
        $response = $ev->getResponse();
        $this->reduceHeaders($ev);
    }

    public static function getSubscribedEvents() {
        return [
          KernelEvents::RESPONSE => ['oneResponse']
        ];
    }

    /**
     * @param ResponseEvent $event
     */
    private function reduceHeaders(ResponseEvent $event): void
    {

    }
}