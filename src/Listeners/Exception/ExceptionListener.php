<?php

namespace App\Listeners\Exception;

use App\Exception\MissingDataException;
use App\Response\Base\BaseResponse;
use App\Traits\ExceptionLoggerAwareTrait;
use Psr\Log\LoggerInterface;
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
    use ExceptionLoggerAwareTrait;

    /**
     * If an exception contains this string in the message then it won't be logged, will just be skipped,
     * some exceptions are just false positive, can be discarded, no need to get spammed by 404, etc.
     */
    private const EXCLUDED_STRINGS = [
        "Full authentication is required to access this resource.", // user tries to do something without being logged-in
    ];

    public function __construct(
        private readonly LoggerInterface $logger,
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

        if ($exception instanceof NotFoundHttpException || ($exception instanceof MissingDataException && $exception->isFront())) {
            $msg      = trim(preg_replace("#[\n ]{1,}#", " ", $exception->getMessage()));
            $response = BaseResponse::buildBadRequestErrorResponse($msg)->toJsonResponse();
        } else {
            $response = BaseResponse::buildInternalServerErrorResponse()->toJsonResponse();

            if (!in_array($exception->getMessage(), self::EXCLUDED_STRINGS)) {
                $this->logException($exception);
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