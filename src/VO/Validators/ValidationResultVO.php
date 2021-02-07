<?php
namespace App\VO\Validators;

/**
 * Used in entity/class validation process
 * Class ValidationResultVO
 * @package App\VO\Validators
 */
class ValidationResultVO {

    /**
     * The result of validation
     * @var bool $valid
     */
    private $valid = false;

    /**
     * If the class can/should be validated at all
     * @var bool $validable
     */
    private $validable = false;

    /**
     * Where key is a field name and value is a message
     *
     * @var array $invalid_fields_messages
     */
    private $invalid_fields_messages = [];

    /**
     * @var string $validated_class
     */
    private $validated_class = "";

    /**
     * @return bool
     */
    public function isValid(): bool {
        return $this->valid;
    }

    /**
     * @param bool $valid
     */
    public function setValid(bool $valid): void {
        $this->valid = $valid;
    }

    /**
     * @return array
     */
    public function getInvalidFieldsMessages(): array {
        return $this->invalid_fields_messages;
    }

    /**
     * @param array $invalid_fields_messages
     */
    public function setInvalidFieldsMessages(array $invalid_fields_messages): void {
        $this->invalid_fields_messages = $invalid_fields_messages;
    }

    /**
     * @return string
     */
    public function getValidatedClass(): string {
        return $this->validated_class;
    }

    /**
     * @param string $validated_class
     */
    public function setValidatedClass(string $validated_class): void {
        $this->validated_class = $validated_class;
    }

    /**
     * @return bool
     */
    public function isValidable(): bool {
        return $this->validable;
    }

    /**
     * @param bool $validable
     */
    public function setValidable(bool $validable): void {
        $this->validable = $validable;
    }

    /**
     * @return string
     */
    public function getAllFailedValidationMessagesAsSingleString(): string
    {
        $messages = "";

        foreach( $this->invalid_fields_messages as $field_name => $message )
        {
            $ucfirst_field_name = ucfirst($field_name);
            $messages .= "({$ucfirst_field_name}) " . $message;
        }

        return $messages;
    }

    /**
     * Will yield valid validation result
     * 
     * @return ValidationResultVO
     */
    public static function buildValidResult(): ValidationResultVO
    {
        $validation_result = new ValidationResultVO();
        $validation_result->setValid(true);
        return $validation_result;
    }
}