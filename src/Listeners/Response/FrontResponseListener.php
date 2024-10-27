<?php

namespace App\Listeners\Response;

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

    /**
     * This tells which headers are allowed, this is necessary if some custom headers are added on front
     * else backend will reject request
     *
     * @link https://stackoverflow.com/questions/50603715/axios-not-sending-custom-headers-in-request-possible-cors-issue
     */
    const ACCESS_CONTROL_ORIGIN_HEADER  = "Access-Control-Allow-Origin";
    const ACCESS_CONTROL_ALLOW_HEADERS  = "Access-Control-Allow-Headers";
    private const HEADER_EXPOSE_HEADERS = "Access-Control-Expose-Headers";

    /**
     * This header informs if system is currently disabled. The point is that the information
     * about system being disabled is sent both via websocket and via response.
     *
     * Reason is that websocket might not get pinged on moment when user does the ajax call, so GUI
     * will not know about that, this could lead to the case where system is disabled but someone still
     * managed to make risky calls (like begin job search while it should not happen on this point).
     */
    private const HEADER_IS_SYSTEM_DISABLED = "is-system-disabled";

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
        $response = $this->addCorsHeaders($event);
        $response = $this->addExposedHeaders($response);

        $event->setResponse($response);
    }

    /**
     * Will add cors related headers to allow frontend calling the backend
     *
     * @param ResponseEvent $event
     * @return Response
     */
    private function addCorsHeaders(ResponseEvent $event): Response
    {
        $response = $event->getResponse();

        // todo: allowing any for now, should be set properly at some point in time
        $response->headers->set(self::ACCESS_CONTROL_ALLOW_HEADERS, "*");
        $response->headers->set(self::ACCESS_CONTROL_ORIGIN_HEADER, "*");

        return $response;
    }

    /**
     * - Adds: {@see self::HEADER_EXPOSE_HEADERS},
     * - See: {@link https://stackoverflow.com/a/61674618},
     *
     * @param Response $response
     *
     * @return Response
     *
     * @throws Exception
     */
    private function addExposedHeaders(Response $response): Response
    {
        // todo later
        // if ($this->systemStateService->isSystemDisabled()) {
        //     $response->headers->set(self::HEADER_IS_SYSTEM_DISABLED, $this->systemStateService->isSystemDisabled());
        //     $response->headers->set(self::HEADER_EXPOSE_HEADERS, self::HEADER_IS_SYSTEM_DISABLED);
        // }

        return $response;
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