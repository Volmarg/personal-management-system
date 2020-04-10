<?php

namespace App\Twig\Services;


use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use App\Services\Translator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TranslationService extends AbstractExtension{

    public function getFunctions() {
        return [
            new TwigFunction('translate', [$this, 'translate']),
        ];
    }

    /**
     * @param string $key
     * @return string
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function translate(string $key){
        $translation = (new Translator())->translate($key);
        return $translation;
    }

}