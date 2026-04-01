<?php

namespace App\Security;

use App\Services\Routing\UrlService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles checking if uri should be authenticated or not
 * This is a small class added only as special case to COMPLETELY disable
 * validations for url, every one of them:
 * - jwt. etc
 */
class UriAuthenticator
{
    /**
     * Will check if currently called uri is excluded from jwt authentication logic
     * {@see LexitBundleJwtTokenAuthenticator::EXCLUDED_URI_REGEXES}
     *
     * @return bool
     */
    public static function isUriExcludedFromAuth(): bool
    {
        $regexes = [
            ...UrlService::EXCLUDED_DEV_AND_SYSTEM_URI_REGEXES,
        ];

        $request = Request::createFromGlobals();
        foreach($regexes as $regex){
            if( preg_match("#" . $regex . "#", $request->getRequestUri()) ){
                return true;
            }
        }
        return false;
    }
}