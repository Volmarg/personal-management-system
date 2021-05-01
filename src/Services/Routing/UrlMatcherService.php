<?php

namespace App\Services\Routing;

use App\Controller\Core\Application;
use Exception;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * This class handles logic for finding matching controllers/methods for given url
 *
 * Class UrlMatcherService
 * @package App\Service\Routing
 */
class UrlMatcherService
{
    const URL_MATCHER_RESULT_CONTROLLER_WITH_METHOD = "_controller";

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var UrlMatcherInterface $urlMatcher
     */
    private UrlMatcherInterface $urlMatcher;

    public function __construct(UrlMatcherInterface $urlMatcher, Application $app)
    {
        $this->app        = $app;
        $this->urlMatcher = $urlMatcher;
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
            $dataArray = $this->urlMatcher->match($url);
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e, [
                "No class with method was found for url", [
                    "url" => $url,
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

}