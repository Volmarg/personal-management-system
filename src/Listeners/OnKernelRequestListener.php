<?php

namespace App\Listeners;

use App\Services\Exceptions\SecurityException;
use App\Services\Core\Logger;
use App\Services\Core\Translator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Security layer for logging every single page call
 *  - first of all for security reason
 *  - second the request data might end up in log so if something fails during insert it might be recovered this way
 * Class OnKernelRequestListener
 */
class OnKernelRequestListener implements EventSubscriberInterface {

    const LOGGER_REQUEST_URL       = "requestUrl";
    const LOGGER_REQUEST_METHOD    = "requestMethod";
    const LOGGER_REQUEST_GET_DATA  = "requestGetData";
    const LOGGER_REQUEST_POST_DATA = "requestPostData";
    const LOGGER_REQUEST_IP        = "requestIp";
    const LOGGER_REQUEST_CONTENT   = "requestContent";
    const LOGGER_REQUEST_HEADERS   = "requestHeaders";

    const ALLOWED_REQUEST_TYPES = [
        "POST",
        "GET",
    ];

    // adjust when needed
    const ALLOWED_IPS = [
        "127.0.0.1",
        "192.168.43.100"
    ];

    /**
     * @var Logger $security_logger
     */
    private $security_logger;

    /**
     * @var \App\Services\Core\Translator $translator
     */
    private $translator;

    public function __construct(Logger $security_logger, Translator $translator) {
        $this->security_logger = $security_logger->getSecurityLogger();
        $this->translator      = $translator;
    }

    /**
     * @param RequestEvent $ev
     * @throws SecurityException
     * 
     */
    public function onRequest(RequestEvent $ev)
    {
        $this->logRequest($ev);
        $this->blockRequestTypes($ev);
        //$this->blockIp($ev); #unlock for personal needs
    }

    public static function getSubscribedEvents() {
        return [
          KernelEvents::REQUEST => ['onRequest']
        ];
    }

    /**
     * @param RequestEvent $event
     */
    private function logRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $method     = $request->getMethod();
        $get_data   = json_encode($request->query->all());
        $post_data  = json_encode($request->request->all());
        $ip         = $request->getClientIp();
        $content    = $request->getContent();
        $headers    = json_encode($request->headers->all());
        $url        = $request->getUri();

        $this->security_logger->info("Visited url", [
            self::LOGGER_REQUEST_URL       => $url,
            self::LOGGER_REQUEST_METHOD    => $method,
            self::LOGGER_REQUEST_GET_DATA  => $get_data,
            self::LOGGER_REQUEST_POST_DATA => $post_data,
            self::LOGGER_REQUEST_IP        => $ip,
            self::LOGGER_REQUEST_CONTENT   => $content,
            self::LOGGER_REQUEST_HEADERS   => $headers,
        ]);
    }

    /**
     * @param RequestEvent $event
     * @throws SecurityException
     * 
     */
    private function blockRequestTypes(RequestEvent $event): void
    {
        $request_method = $event->getRequest()->getMethod();

        if( !in_array($request_method, self::ALLOWED_REQUEST_TYPES) ){

            $response = new Response();
            $response->setContent("");

            $event->stopPropagation();
            $event->setResponse($response);

            $log_message       = $this->translator->translate("logs.security.visitedPageWithUnallowedMethod");
            $exception_message = $this->translator->translate('exceptions.security.youAreNotAllowedToSeeThis');

            $this->security_logger->info($log_message);
            throw new SecurityException($exception_message);
        }

    }

    /**
     * @param RequestEvent $event
     * @throws SecurityException
     * 
     */
    private function blockIp(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $ip      = $request->getClientIp();

        if( !in_array($ip, self::ALLOWED_IPS) ){

            $response = new Response();
            $response->setContent("");

            $event->stopPropagation();
            $event->setResponse($response);

            $log_message       = $this->translator->translate("logs.security.visitedPageWithUnallowedIp");
            $exception_message = $this->translator->translate('exceptions.security.youAreNotAllowedToSeeThis');

            $this->security_logger->info($log_message);
            throw new SecurityException($exception_message);
        }

    }
}