<?php

namespace App\Services;

use App\Services\Validation\ValidationService;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class RequestService
{
    /**
     * Attempt getting request body as array, if json  data is not present in body then exception is thrown
     *
     * @throws Exception
     */
    public static function tryFromJsonBody(Request $request): array
    {
        $body = $request->getContent();
        if (!ValidationService::isJsonValid($body)) {
            throw new Exception("Body content is not a valid JSON. Last json error: " . json_last_error());
        }

        return json_decode($body, true);
    }
}