<?php

namespace App\Services\TypeProcessor;

use function Symfony\Component\String\u;

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

    /**
     * Takes for example properties names and return them in human friendly form.
     * This is for example used to notify user about missing form data, so:
     * - thing like: targetGroupId,
     * - gets transformed to "target group"
     * - etc.
     *
     * @param string $string
     *
     * @return string
     */
    public static function toHumanFriendly(string $string): string
    {
        /** @link https://stackoverflow.com/a/6254138 */
        $humanReadable = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $string);

        return u($humanReadable)->lower()->title();
    }
}