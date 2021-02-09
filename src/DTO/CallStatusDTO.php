<?php

namespace App\DTO;

use App\Services\Core\Translator;

class CallStatusDTO extends AbstractDTO {

    const KEY_FAILURE_REASON_FORM_VALIDATION       = "KEY_FAILURE_REASON_FORM_VALIDATION";
    const KEY_FAILURE_REASON_DUPLICATED_RECORD     = "KEY_FAILURE_REASON_DUPLICATED_RECORD";
    const KEY_FAILURE_REASON_INTERNAL_SERVER_ERROR = "KEY_FAILURE_REASON_INTERNAL_SERVER_ERROR";

    /**
     * @var bool $isSuccess
     */
    private $isSuccess = false;

    /**
     * @var string $code
     */
    private $code = 500;

    /**
     * @var string $failureReason
     */
    private $failureReason = '';

    /**
     * @var string
     */
    private $message = '';

    /**
     * @return string
     * 
     */
    public function getMessage(): string {

        if( !empty($this->message) ){
            return $this->message;
        }

        $translator = new Translator();

        if( $this->isSuccess){
            $message = $translator->translate("messages.ajax.success.recordHasBeenCreated");
        } elseif( self::KEY_FAILURE_REASON_FORM_VALIDATION == $this->failureReason ){
            $message = $translator->translate("messages.ajax.failure.formValidationFailed");
        } elseif( self::KEY_FAILURE_REASON_FORM_VALIDATION == $this->failureReason ){
            $message = $translator->translate("messages.ajax.failure.internalServerError");
        } elseif( self::KEY_FAILURE_REASON_DUPLICATED_RECORD == $this->failureReason ){
            $message = $translator->translate("messages.ajax.failure.duplicatedRecord");
        } else{
            $message = $translator->translate("messages.ajax.failure.undefinedError");
        }

        return $message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCode(): string {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getIsSuccess(): string {
        return $this->isSuccess;
    }

    /**
     * @param string $isSuccess
     */
    public function setIsSuccess(string $isSuccess): void {
        $this->isSuccess = $isSuccess;
    }

    /**
     * @return string
     */
    public function getFailureReason(): string {
        return $this->failureReason;
    }

    /**
     * @param string $failureReason
     */
    public function setFailureReason(string $failureReason): void {
        $this->failureReason = $failureReason;
    }

}