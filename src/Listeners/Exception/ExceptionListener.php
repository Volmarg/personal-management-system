<?php

namespace App\Listeners\Exception;

use App\Response\Base\BaseResponse;
use App\Services\Core\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Exceptions handling class
 *
 * Class ExceptionListener
 * @package App\Listener
 */
class ExceptionListener implements EventSubscriberInterface
{
    /**
     * If an exception contains this string in the message then it won't be logged, will just be skipped,
     * some exceptions are just false positive, can be discarded, no need to get spammed by 404, etc.
     */
    private const EXCLUDED_STRINGS = [
        "Full authentication is required to access this resource.", // user tries to do something without being logged-in
    ];

    /**
     * @param Logger $logger
     */
    public function __construct(
        private readonly Logger $logger
    ) {
    }

    /**
     * Handles the exceptions
     *
     * @param ExceptionEvent $event
     */
    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (
                $exception instanceof NotFoundHttpException
            // ||  $exception instanceof PublicFolderAccessDeniedException todo at some point
        ) {
            $msg      = trim(preg_replace("#[\n ]{1,}#", " ", $exception->getMessage()));
            $response = BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        } else {
            $response = BaseResponse::buildInternalServerErrorResponse()->toJsonResponse();

            if (!in_array($exception->getMessage(), self::EXCLUDED_STRINGS)) {
                $this->logger->logException($exception);
            }
        }

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                "onException", -1
            ],
        ];
    }

}