<?php

namespace App\Services\TypeProcessor;

class TextHandler
{
    /**
     * @param int    $maxLen
     * @param string $text
     *
     * @return string
     */
    public static function shortenWithDots(string $text, int $maxLen): string
    {
        if (strlen($text) <= $maxLen) {
            return $text;
        }

        return substr($text, 0, $maxLen) . "...";
    }
}