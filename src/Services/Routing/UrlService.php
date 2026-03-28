<?php

namespace App\Services\Routing;

use Symfony\Component\HttpFoundation\Request;

class UrlService
{
    private const string EXCLUDE_PROFILER_REGEX = "^/_profiler";
    private const string EXCLUDE_WDT_REGEX      = "^/_wdt";
    private const string EXCLUDE_FRAGMENT_REGEX = "^/_fragment";

    public const EXCLUDED_DEV_AND_SYSTEM_URI_REGEXES = [
        self:: EXCLUDE_FRAGMENT_REGEX,
        self:: EXCLUDE_PROFILER_REGEX,
        self:: EXCLUDE_WDT_REGEX,
    ];

    /**
     * @return bool
     */
    public static function isExcludedDevOrSystemUri(): bool
    {
        $request = Request::createFromGlobals();
        foreach (self::EXCLUDED_DEV_AND_SYSTEM_URI_REGEXES as $regex) {
            if (preg_match("#" . $regex . "#", $request->getRequestUri())) {
                return true;
            }
        }

        return false;
    }
}