<?php

namespace App\Controller\Core;

use App\Services\Session\AjaxCallsSessionService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxResponse extends AbstractController {

    const KEY_CODE           = "code";
    const KEY_MESSAGE        = "message";
    const KEY_TEMPLATE       = "template";
    const KEY_PASSWORD       = "password";
    const KEY_RELOAD_PAGE    = "reload_page";
    const KEY_RELOAD_MESSAGE = "reload_message";

    const XML_HTTP_HEADER_KEY   = "X-Requested-With";
    const XML_HTTP_HEADER_VALUE = "XMLHttpRequest";

    /**
     * @param int $code
     * @param string $message
     * @param string|null $template
     * @param string|null $password
     * @param bool|null $reload_page
     * @param string $reload_message
     * @return JsonResponse
     * @throws Exception
     */
    public static function buildResponseForAjaxCall(
        int     $code,
        string  $message,
        ?string $template = null,
        ?string $password = null,
        ?bool   $reload_page = false,
        string  $reload_message = ""
    ): JsonResponse {

        $response_data = [
            self::KEY_CODE    => $code,
            self::KEY_MESSAGE => $message,
        ];

        if( !empty($template) ){
            $response_data[self::KEY_TEMPLATE] = $template;
        }

        if( !empty($password) ){
            $response_data[self::KEY_PASSWORD] = $password;
        }

        if( !$reload_page ){
            $reload_page = self::getPageReloadStateFromSession();
        }

        if( $reload_page ){
            $reload_message = self::getPageReloadMessageFromSession();
        }

        $response_data[self::KEY_RELOAD_PAGE]    = $reload_page;
        $response_data[self::KEY_RELOAD_MESSAGE] = $reload_message;

        $response = new JsonResponse($response_data, 200);
        return $response;
    }

    /**
     * Checking if maybe somewhere in whole lifetime some event, kernel etc. emitted this data to session,
     *  as it's impossible to pass such data directly to controller/service from some places of the project
     * @return bool
     * @throws Exception
     */
    private static function getPageReloadStateFromSession(): bool
    {
        if( !AjaxCallsSessionService::hasPageReloadAfterAjaxCall() ){
            return false;
        }

        $reload_page = AjaxCallsSessionService::getPageReloadAfterAjaxCall();
        return $reload_page;
    }

    /**
     * Checking if maybe somewhere in whole lifetime some event, kernel etc. emitted this data to session,
     *  as it's impossible to pass such data directly to controller/service from some places of the project
     * @return string
     * @throws Exception
     */
    private static function getPageReloadMessageFromSession():string
    {
        if( !AjaxCallsSessionService::hasPageReloadMessageAfterAjaxCall() ){
            return "";
        }

        $message = AjaxCallsSessionService::getPageReloadMessageAfterAjaxCall();
        return $message;
    }
}
