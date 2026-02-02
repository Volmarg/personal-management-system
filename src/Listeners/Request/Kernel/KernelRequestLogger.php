<?php

namespace App\Listeners\Request\Kernel;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest', priority: 3)]
class KernelRequestLogger
{
    public function __construct(
        private readonly LoggerInterface $requestLogger,
    ) {
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $method   = $request->getMethod();
        $getData  = json_encode($request->query->all());
        $postData = json_encode($request->request->all());
        $ip       = $request->getClientIp();
        $content  = $request->getContent();
        $headers  = json_encode($request->headers->all());
        $url      = $request->getUri();

        $this->requestLogger->info("Visited url", [
            "requestUrl"      => $url,
            "requestMethod"   => $method,
            "requestGetData"  => $getData,
            "requestPostData" => $postData,
            "requestIp"       => $ip,
            "requestContent"  => $content,
            "requestHeaders"  => $headers,
        ]);
    }
}