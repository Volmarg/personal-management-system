<?php

namespace App\Services;

use App\Response\Base\BaseResponse;
use App\Services\Routing\UrlMatcherService;
use Symfony\Component\HttpFoundation\Request;

class ResponseService
{
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

}