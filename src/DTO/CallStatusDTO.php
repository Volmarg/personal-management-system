<?php

namespace App\DTO;

use App\Services\Core\Translator;

class CallStatusDTO extends AbstractDTO {

    const KEY_FAILURE_REASON_FORM_VALIDATION       = "KEY_FAILURE_REASON_FORM_VALIDATION";
    const KEY_FAILURE_REASON_DUPLICATED_RECORD     = "KEY_FAILURE_REASON_DUPLICATED_RECORD";
    const KEY_FAILURE_REASON_INTERNAL_SERVER_ERROR = "KEY_FAILURE_REASON_INTERNAL_SERVER_ERROR";

    /**
     * @var bool $is_success
     */
    private $is_success = false;

    /**
     * @var string $code
     */
    private $code = 500;

    /**
     * @var string $failure_reason
     */
    private $failure_reason = '';

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

        if( $this->is_success){
            $message = $translator->translate("messages.ajax.success.recordHasBeenCreated");
        } elseif( self::KEY_FAILURE_REASON_FORM_VALIDATION == $this->failure_reason ){
            $message = $translator->translate("messages.ajax.failure.formValidationFailed");
        } elseif( self::KEY_FAILURE_REASON_FORM_VALIDATION == $this->failure_reason ){
            $message = $translator->translate("messages.ajax.failure.internalServerError");
        } elseif( self::KEY_FAILURE_REASON_DUPLICATED_RECORD == $this->failure_reason ){
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
        return $this->is_success;
    }

    /**
     * @param string $is_success
     */
    public function setIsSuccess(string $is_success): void {
        $this->is_success = $is_success;
    }

    /**
     * @return string
     */
    public function getFailureReason(): string {
        return $this->failure_reason;
    }

    /**
     * @param string $failure_reason
     */
    public function setFailureReason(string $failure_reason): void {
        $this->failure_reason = $failure_reason;
    }

}