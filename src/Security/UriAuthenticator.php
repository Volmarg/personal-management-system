<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;

/**
 * Handles checking if uri should be authenticated or not
 * This is a small class added only as special case to COMPLETELY disable
 * validations for url, every one of them:
 * - jwt. etc
 */
class UriAuthenticator
{
    const EXCLUDE_PROFILER_REGEX = "^/_profiler";
    const EXCLUDE_WDT_REGEX      = "^/_wdt";
    const EXCLUDE_FRAGMENT_REGEX = "^/_fragment";
    const EXCLUDE_DOWNLOAD_REGEX = "^/download";

    /**
     * By default {@see JwtAuthenticationDisabledAttribute} should be added on top of route based method
     * to disable the jwt check, however it's not always possible so this solution (regex patter) should
     * be used in such cases.
     *
     * Besides, this array is used for other checks like for example csrf token validation
     */
    const EXCLUDED_URI_REGEXES = [
        self:: EXCLUDE_FRAGMENT_REGEX,
        self:: EXCLUDE_PROFILER_REGEX,
        self:: EXCLUDE_WDT_REGEX,
        self:: EXCLUDE_DOWNLOAD_REGEX,
    ];

    /**
     * Will check if currently called uri is excluded from jwt authentication logic
     * {@see LexitBundleJwtTokenAuthenticator::EXCLUDED_URI_REGEXES}
     *
     * @return bool
     */
    public static function isUriExcludedFromAuthenticationByRegex(): bool
    {
        $request = Request::createFromGlobals();
        foreach(self::EXCLUDED_URI_REGEXES as $regex){
            if( preg_match("#" . $regex . "#", $request->getRequestUri()) ){
                return true;
            }
        }
        return false;
    }

}