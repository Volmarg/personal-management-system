<?php
namespace App\Controller\Utils;

use App\Services\Exceptions\ExceptionDuplicatedTranslationKey as ExceptionDuplicatedTranslationKeyAlias;
use App\Services\Translator;

/**
 * Class Translations
 * This class contains the most often occurring strings translations
 * @package App\Controller\Utils
 */
class Translations {

    /**
     * @var Translator $translator
     */
    private $translator;

    public function __construct() {
        $this->translator = new Translator();
    }

    /**
     * @throws ExceptionDuplicatedTranslationKeyAlias
     */
    public function ajaxSuccessRecordHasBeenRemoved(){
        $message = $this->translator->translate("messages.ajax.success.recordHasBeenRemoved");
        return $message;
    }

    /**
     * @throws ExceptionDuplicatedTranslationKeyAlias
     */
    public function ajaxFailureRecordCouldNotBeenRemoved(){
        $message = $this->translator->translate("messages.ajax.failure.couldNotRemoveRecord");
        return $message;
    }
}