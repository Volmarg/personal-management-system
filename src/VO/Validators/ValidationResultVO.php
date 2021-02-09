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
     * @var array $invalidFieldsMessages
     */
    private $invalidFieldsMessages = [];

    /**
     * @var string $validatedClass
     */
    private $validatedClass = "";

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
        return $this->invalidFieldsMessages;
    }

    /**
     * @param array $invalidFieldsMessages
     */
    public function setInvalidFieldsMessages(array $invalidFieldsMessages): void {
        $this->invalidFieldsMessages = $invalidFieldsMessages;
    }

    /**
     * @return string
     */
    public function getValidatedClass(): string {
        return $this->validatedClass;
    }

    /**
     * @param string $validatedClass
     */
    public function setValidatedClass(string $validatedClass): void {
        $this->validatedClass = $validatedClass;
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

        foreach($this->invalidFieldsMessages as $fieldName => $message )
        {
            $ucfirstFieldName = ucfirst($fieldName);
            $messages .= "({$ucfirstFieldName}) " . $message;
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
        $validationResult = new ValidationResultVO();
        $validationResult->setValid(true);
        return $validationResult;
    }
}