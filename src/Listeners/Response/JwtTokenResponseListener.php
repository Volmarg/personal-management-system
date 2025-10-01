<?php

namespace App\Listeners\Response;

use App\Attribute\JwtAuthenticationDisabledAttribute;
use App\Response\Base\BaseResponse;
use App\Security\UriAuthenticator;
use App\Services\Attribute\AttributeReaderService;
use App\Services\ResponseService;
use App\Services\Routing\UrlMatcherService;
use App\Services\Security\JwtAuthenticationService;
use App\Traits\ExceptionLoggerAwareTrait;
use Exception;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Handles the jwt token in response, will refresh the provided one and set it back in response
 *
 * Class FrontResponseListener
 * @package App\Listener
 */
class JwtTokenResponseListener implements EventSubscriberInterface
{
    use ExceptionLoggerAwareTrait;

    const ROUTES_EXCLUDED_FROM_TOKEN_REFRESH = [
      // UserAction::ROUT_NAME_REMOVE_USER,
    ];

    public function __construct(
        private readonly SerializerInterface      $serializer,
        private readonly ResponseService          $responseService,
        private readonly JwtAuthenticationService $jwtAuthenticationService,
        private readonly UrlMatcherService        $urlMatcherService,
        private readonly AttributeReaderService   $attributeReaderService,
        private readonly LoggerInterface          $logger
    ) {
    }

    /**
     * Handles the response, attempts to refresh the token and append it to the base response dto json content
     *
     * @param ResponseEvent $event
     * @throws ReflectionException
     * @throws Exception
     */
    public function onResponse(ResponseEvent $event): void
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        if (Request::METHOD_OPTIONS === $request->getMethod()) {
            return;
        }

        if (!($response instanceof JsonResponse)) {
            return;
        }

        if (!$this->responseService->canHandleAsBaseResponse($request)) {
            return;
        }

        if (
                UriAuthenticator::isUriExcludedFromAuthenticationByRegex() // must be first due to profiler falling in this case yet crashes for other checks (Symfony issue)
            ||  $this->attributeReaderService->hasUriAttribute($request->getRequestUri(), JwtAuthenticationDisabledAttribute::class)
        ) {
            return;
        }

        // that's because each api response must extend from base so this key must be present
        $dataArray = json_decode($response->getContent(), true);
        $fqn       = $dataArray[BaseResponse::KEY_FQN] ?? null;
        if (is_null($fqn)) {
            throw new Exception("Given response was not based on the parent: " . BaseResponse::class);
        }

        /**
         * this case happens when child class has no {@see BaseResponse::fromJson()} implemented and uses the parent one
         * that's desired effect as some child classes can have some special checks and only then they implement this method
         */
        $frontResponse = $fqn::fromJson($response->getContent());
        if ($fqn !== $frontResponse::class) {
            $frontResponse = $this->serializer->deserialize($response->getContent(), $fqn, "json");
        }

        if (!$frontResponse->isSuccess()) {
            return;
        }

        $jwtToken = $this->jwtAuthenticationService->extractJwtFromRequest();
        if (empty($jwtToken)) {
            return;
        }

        $routeForUri = $this->urlMatcherService->getRouteForCalledUri($request->getRequestUri());
        if (!empty($routeForUri) && in_array($routeForUri, self::ROUTES_EXCLUDED_FROM_TOKEN_REFRESH)) {
            return;
        }

        try {
            $refreshedJwtToken = $this->jwtAuthenticationService->handleJwtTokenRefresh($jwtToken);
            $frontResponse->setToken($refreshedJwtToken);
            $frontResponse->setSuccess(true);
        } catch (Exception $e) {

            if (JwtAuthenticationService::isJwtTokenException($e)) {
                // no matter what happens - cannot let the user in!
                $frontResponse->setCode(Response::HTTP_UNAUTHORIZED);
                $frontResponse->setMessage($e->getMessage());
            } else {

                $frontResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                $frontResponse->setRedirectRoute("Exception was thrown");
                $this->logException($e);
            }

            $frontResponse->setSuccess(false);
        }

        $event->setResponse($frontResponse->toJsonResponse());
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                "onResponse",
                -49,
            ],
        ];
    }

}