<?php

namespace App\Twig\Services;


use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use App\Services\Core\Translator;
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
        $translation = (new \App\Services\Core\Translator())->translate($key);
        return $translation;
    }

}