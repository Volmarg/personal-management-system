<?php

namespace App\Services\Routing;

use App\Services\Core\Logger;
use App\Traits\ExceptionLoggerAwareTrait;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * This class handles logic for finding matching controllers/methods for given url
 *
 * Class UrlMatcherService
 * @package App\Service\Routing
 */
class UrlMatcherService
{
    use ExceptionLoggerAwareTrait;

    const URL_MATCHER_RESULT_CONTROLLER_WITH_METHOD = "_controller";

    const URL_MATCHER_RESULT_ROUTE = "_route";

    /**
     * @var UrlMatcherInterface $urlMatcher
     */
    private UrlMatcherInterface $urlMatcher;

    public function __construct(
        UrlMatcherInterface $urlMatcher,
        private readonly Logger $logger
    )
    {
        $this->urlMatcher   = $urlMatcher;
    }

    /**
     * Will return `class::method` for given url or null if nothing was found
     *
     * @param string $url
     * @return string|null
     */
    public function getClassAndMethodForCalledUrl(string $url): ?string
    {
        try{
            $normalizedUrl = $url;
            // if url contains query params then matcher just crashes
            if (str_contains($url, "?")) {
                $parts = explode("?", $url);
                $normalizedUrl = $parts[0];
            }

            $dataArray = $this->urlMatcher->match($normalizedUrl);
        }catch(Exception $e){
            $this->logException($e, [
                "No class with method was found for url", [
                    "url" => $url,
                    'normalizedUrl' => $normalizedUrl,
                ]
            ]);
            return null;
        }

        return $dataArray[self::URL_MATCHER_RESULT_CONTROLLER_WITH_METHOD];
    }

    /**
     * Will return `class` separated from `class::method` for given url or null if nothing was found
     *
     * @param string $url
     * @return string|null
     */
    public function getClassForCalledUrl(string $url): ?string
    {
        $classAndMethod = $this->getClassAndMethodForCalledUrl($url);
        if( empty($classAndMethod) ){
            return null;
        }

        $explodedClassAndMethod = explode("::", $classAndMethod);
        $class                  = $explodedClassAndMethod[0];

        return $class;
    }

    /**
     * Will return matching route for called uri
     *
     * @param string $uri
     * @return string|null
     */
    public function getRouteForCalledUri(string $uri): ?string
    {
        try{
            $uriWithoutQueryParams = preg_replace("#\?.*#", "", $uri);

            $dataArray = $this->urlMatcher->match($uriWithoutQueryParams);
            $route     = $dataArray[self::URL_MATCHER_RESULT_ROUTE];
        } catch (Exception $exc) {
            /**
             * This is added because browsers are doing {@see Request::METHOD_OPTIONS} calls by default,
             * and there is special handling of such requests in this project. Everything works fine but the OPTIONS calls
             * are running in here and are generating a bunch of useless warns.
             */
            $request = Request::createFromGlobals();
            if ($request->getMethod() !== Request::METHOD_OPTIONS) {
                $this->logger->getLogger()->warning("No route found for called uri", [
                    "uri"       => $uri,
                    "request"   => [
                        "method" => $request->getMethod(),
                    ],
                    'exception' => [
                        "message" => $exc->getMessage(),
                        "trace"   => explode("\n", $exc->getTraceAsString()),
                        "class"   => $exc::class,
                    ],

                ]);
            }

            return null;
        }

        return $route;
    }

}