<?php

namespace App\Listeners\Request\Kernel;

use App\Response\Base\BaseResponse;
use App\Services\ConfigLoaders\ConfigLoaderSecurity;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest', priority: 2)]
class KernelRequestIpBlock
{
    public function __construct(
        private readonly ConfigLoaderSecurity $configLoaderSecurity,
        private readonly LoggerInterface      $securityLogger,
    ) {
    }

    /**
     * @param RequestEvent $event
     *
     * @throws Exception
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $restrictedIps = $this->configLoaderSecurity->getRestrictedIps();
        $request       = $event->getRequest();
        $ip            = $request->getClientIp();

        if (empty($restrictedIps)) {
            return;
        }

        if (!in_array($ip, $restrictedIps)) {
            $msg      = "Not allowed to access from this ip: {$ip}";
            $response = BaseResponse::buildAccessDeniedResponse($msg);

            $event->stopPropagation();
            $event->setResponse($response->toJsonResponse());

            $this->securityLogger->info($msg, [
                "ip" => $ip,
            ]);
        }
    }
}