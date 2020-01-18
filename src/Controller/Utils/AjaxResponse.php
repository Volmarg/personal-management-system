<?php

namespace App\Controller\Utils;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxResponse extends AbstractController {

    const KEY_CODE      = "code";
    const KEY_MESSAGE   = "message";
    const KEY_TEMPLATE  = "template";

    /**
     * @param int $code
     * @param string $message
     * @param string|null $template
     * @return JsonResponse
     */
    public static function buildResponseForAjaxCall(int $code, string $message, string $template = null): JsonResponse {

        $response_data = [
            self::KEY_CODE    => $code,
            self::KEY_MESSAGE => $message,
        ];

        if( !empty($template) ){
            $response_data[self::KEY_TEMPLATE] = $template;
        }

        $response = new JsonResponse($response_data, 200);
        return $response;
    }
}
