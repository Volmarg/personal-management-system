<?php

namespace App\Controller\Utils;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxResponse extends AbstractController {

    const KEY_CODE      = "code";
    const KEY_MESSAGE   = "message";
    const KEY_TEMPLATE  = "template";
    const KEY_PASSWORD  = "password";

    /**
     * @param int $code
     * @param string $message
     * @param string|null $template
     * @param string|null $password
     * @return JsonResponse
     */
    public static function buildResponseForAjaxCall(
        int    $code,
        string $message,
        string $template = null,
        string $password = null ): JsonResponse {

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

        $response = new JsonResponse($response_data, 200);
        return $response;
    }
}
