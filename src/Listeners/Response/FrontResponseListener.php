<?php

namespace App\Listeners\Response;

use App\Services\ResponseService;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handles the response made from frontend
 *
 * Class FrontResponseListener
 * @package App\Listener
 */
class FrontResponseListener implements EventSubscriberInterface
{

    public function __construct(
        // private readonly SystemStateService $systemStateService
    )
    {
    }

    /**
     * Handles the response, adds custom headers etc.
     *
     * @param ResponseEvent $event
     * @throws Exception
     */
    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->setStatusCode(Response::HTTP_OK); // 200 on purpose, no matter what happens front must handle response
        $response = ResponseService::addCorsHeaders($response);
        $response = ResponseService::addExposedHeaders($response);

        $event->setResponse($response);
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                "onResponse" , -50
            ],
        ];
    }
}