<?php

namespace App\Services\Core;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class is a Legacy - used to have custom logic for multiple translation files but as more translations came in
 * the slower it became, up to the point where it was impossible to work with the project on slower pc
 * This is left to avoid extensive refactor by replacing calls for translate method and injecting translator all over
 * instead new command was added to build output translation file from assets/translations
 * Class Translator
 * @package App\Services\Core
 */
class Translator extends AbstractController {

    /**
     * Must be static!
     * @var TranslatorInterface $translator
     */
    private static $translator;

    public static function setTranslator(TranslatorInterface $translator)
    {
        self::$translator = $translator;
    }

    /**
     * @param string $searchedKey
     * @param array $params
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function translate(string $searchedKey, array $params = [], string $domain = null, string $locale = null): string {
        $translation = self::$translator->trans($searchedKey, $params, $domain, $locale);
        return $translation;
    }

}