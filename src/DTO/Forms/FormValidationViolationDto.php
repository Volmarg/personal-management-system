<?php

namespace App\DTO\Forms;

/**
 * Class FormValidationViolationDto
 */
class FormValidationViolationDto
{
    const FIELD_NAME        = "field_name";
    const VIOLATION_MESSAGE = "violation_message";

    private string $fieldName;
    private string $violationMessage;

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     */
    public function setFieldName(string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return string
     */
    public function getViolationMessage(): string
    {
        return $this->violationMessage;
    }

    /**
     * @param string $violationMessage
     */
    public function setViolationMessage(string $violationMessage): void
    {
        $this->violationMessage = $violationMessage;
    }

    /**
     * Build the violation dto for given field name and message
     * @param string $fieldName
     * @param string $message
     * @return FormValidationViolationDto
     */
    public static function buildForFieldNameAndMessage(string $fieldName, string $message): FormValidationViolationDto
    {
        $dto = new FormValidationViolationDto();
        $dto->setFieldName($fieldName);
        $dto->setViolationMessage($message);

        return $dto;
    }

    /**
     * Returns the array representation of the dto
     * @return string[]
     */
    public function toArray()
    {
        return [
            self::FIELD_NAME        => $this->getFieldName(),
            self::VIOLATION_MESSAGE => $this->getViolationMessage(),
        ];
    }

    /**
     * Returns the array representation of the dto but each key is field name and value is a message
     * @return string[]
     */
    public function toFieldMessageArray()
    {
        return[
            $this->getFieldName() => $this->getViolationMessage()
        ];
    }

}