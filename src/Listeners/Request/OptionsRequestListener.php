<?php

namespace App\Listeners\Request;

use App\Listeners\Response\FrontResponseListener;
use App\Response\Base\BaseResponse;
use App\Services\ResponseService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This listener exists to solve the browser-generated {@see Request::METHOD_OPTIONS}.
 *
 * If such request is sent then it must be denied from reaching the controllers else it generates
 * method not allowed errors.
 *
 * If method had been allowed on the target function then it would execute the function itself.
 *
 * Calling the {@see ResponseService} to set the headers is needed, because the request propagation gets stopped,
 * and thus it never reaches the {@see FrontResponseListener} which normally sets the headers for all responses.
 */
class OptionsRequestListener implements EventSubscriberInterface
{

    /**
     * @param RequestEvent $ev
     */
    public function onRequest(RequestEvent $ev): void
    {
        if (Request::METHOD_OPTIONS === $ev->getRequest()->getMethod()) {
            $response = BaseResponse::buildOkResponse()->toJsonResponse();
            $response = ResponseService::addCorsHeaders($response);

            $ev->stopPropagation();
            $ev->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 10000],
        ];
    }

}