<?php

namespace App\Twig\Core;

use App\Controller\Utils\Utils as CoreUtils;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Utils extends AbstractExtension {


    public function getFunctions() {
        return [
            new TwigFunction('roundDownToAny', [$this, 'roundDownToAny']),
            new TwigFunction('secondsToTimeFormat', [$this, 'secondsToTimeFormat']),
            new TwigFunction('getCurrUri', [$this, 'getCurrUri']),
        ];
    }

    /**
     * Will round the given value to the nearest value provided as second parameter, for example nearest 0.25
     * 1.0, 1.25, 1.5, 1,75 ....
     *
     * @param float $actualValue
     * @param float $roundToRepentanceOf
     * @return float|int
     */
    public function roundDownToAny(float $actualValue, float $roundToRepentanceOf) {
        return CoreUtils::roundDownToAny($actualValue, $roundToRepentanceOf);
    }

    /**
     * Will convert seconds to time based format
     * Example: 20:35:15
     *
     * @param int $seconds
     * @return string
     */
    public function secondsToTimeFormat(int $seconds): string
    {
       return CoreUtils::secondsToTimeFormat($seconds);
    }

    /**
     * Returns current URI
     *
     * @return string
     */
    public function getCurrUri(): string
    {
        $request = Request::createFromGlobals();
        return $request->getUri();
    }
}