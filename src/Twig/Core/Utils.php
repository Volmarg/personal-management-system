<?php

namespace App\Twig\Core;

use App\Controller\Utils\Utils as CoreUtils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Utils extends AbstractExtension {


    public function getFunctions() {
        return [
            new TwigFunction('roundDownToAny', [$this, 'roundDownToAny']),
            new TwigFunction('secondsToTimeFormat', [$this, 'secondsToTimeFormat']),
        ];
    }

    /**
     * Will round the given value to the nearest value provided as second parameter, for example nearest 0.25
     * 1.0, 1.25, 1.5, 1,75 ....
     *
     * @param float $actual_value
     * @param float $round_to_repentance_of
     * @return float|int
     */
    public function roundDownToAny(float $actual_value, float $round_to_repentance_of) {
        return CoreUtils::roundDownToAny($actual_value, $round_to_repentance_of);
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
}