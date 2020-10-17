<?php

namespace App\DTO\Forms;

/**
 * Class FormValidationViolationDto
 */
class FormValidationViolationDto
{
    const FIELD_NAME        = "field_name";
    const VIOLATION_MESSAGE = "violation_message";

    private string $field_name;
    private string $violation_message;

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->field_name;
    }

    /**
     * @param string $field_name
     */
    public function setFieldName(string $field_name): void
    {
        $this->field_name = $field_name;
    }

    /**
     * @return string
     */
    public function getViolationMessage(): string
    {
        return $this->violation_message;
    }

    /**
     * @param string $violation_message
     */
    public function setViolationMessage(string $violation_message): void
    {
        $this->violation_message = $violation_message;
    }

    /**
     * Build the violation dto for given field name and message
     * @param string $field_name
     * @param string $message
     * @return FormValidationViolationDto
     */
    public static function buildForFieldNameAndMessage(string $field_name, string $message): FormValidationViolationDto
    {
        $dto = new FormValidationViolationDto();
        $dto->setFieldName($field_name);
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