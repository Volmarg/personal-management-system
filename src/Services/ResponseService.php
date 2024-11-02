<?php

namespace App\Services;

use App\Response\Base\BaseResponse;
use App\Services\Routing\UrlMatcherService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseService
{
    /**
     * This tells which headers are allowed, this is necessary if some custom headers are added on front
     * else backend will reject request
     *
     * @link https://stackoverflow.com/questions/50603715/axios-not-sending-custom-headers-in-request-possible-cors-issue
     */
    private const ACCESS_CONTROL_ORIGIN_HEADER  = "Access-Control-Allow-Origin";
    private const ACCESS_CONTROL_ALLOW_HEADERS  = "Access-Control-Allow-Headers";
    private const ACCESS_CONTROL_ALLOW_METHODS  = "Access-Control-Allow-Methods";
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

    /**
     * Originally it was planned that this project WILL ALWAYS return json response base on {@see BaseResponse}, but
     * there are some cases where the responses should not be based on it.
     */
    private const ROUTES_EXCLUDED_FROM_HANDLING_AS_BASE_RESPONSE = [
        /**
         * It was made this way to reduce necessity of having some code on front which will extract base64 from
         * response etc... this way the native browser logic will just handle the download.
         */
        // PublicFolderAction::ROUTE_NAME_DOWNLOAD, //todo at some point
    ];

    public function __construct(
        private readonly UrlMatcherService $urlMatcherService
    ) {
    }

    /**
     * Like mentioned in the {@see ResponseService::ROUTES_EXCLUDED_FROM_HANDLING_AS_BASE_RESPONSE} the project responses should be
     * based on the {@see BaseResponse}.
     *
     * So this method will say if certain routes should be excluded from such handling and their response
     * should be forwarded without assuming / using it as {@see BaseResponse}
     *
     * @param Request $request
     *
     * @return bool
     */
    public function canHandleAsBaseResponse(Request $request): bool
    {
        $calledRoute = $this->urlMatcherService->getRouteForCalledUri($request->getRequestUri());
        return !in_array($calledRoute, self::ROUTES_EXCLUDED_FROM_HANDLING_AS_BASE_RESPONSE);
    }

    /**
     * Will add cors related headers to allow frontend calling the backend
     *
     * @param Response $response
     *
     * @return Response
     */
    public static function addCorsHeaders(Response $response): Response
    {
        // todo: allowing any for now, should be set properly at some point in time
        $response->headers->set(self::ACCESS_CONTROL_ALLOW_HEADERS, "*");
        $response->headers->set(self::ACCESS_CONTROL_ORIGIN_HEADER, "*");
        $response->headers->set(self::ACCESS_CONTROL_ALLOW_METHODS, "*");

        return $response;
    }

    /**
     * - Adds: {@see self::HEADER_EXPOSE_HEADERS},
     * - See: {@link https://stackoverflow.com/a/61674618},
     *
     * @param Response $response
     *
     * @return Response
     */
    public static function addExposedHeaders(Response $response): Response
    {
        // todo later
        // if ($this->systemStateService->isSystemDisabled()) {
        //     $response->headers->set(self::HEADER_IS_SYSTEM_DISABLED, $this->systemStateService->isSystemDisabled());
        //     $response->headers->set(self::HEADER_EXPOSE_HEADERS, self::HEADER_IS_SYSTEM_DISABLED);
        // }

        return $response;
    }

}