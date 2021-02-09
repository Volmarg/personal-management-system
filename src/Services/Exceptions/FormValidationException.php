<?php

namespace App\Services\Exceptions;

use App\DTO\Forms\FormValidationViolationDto;
use Symfony\Component\HttpFoundation\Response;

class FormValidationException extends \Exception
{

    /**
     * @var FormValidationViolationDto[] $formValidationViolations
     */
    private array $formValidationViolations;

    /**
     * @param bool $asArrays
     *          - if false then returns array of dtos
     *          - if true then return array of arrays where each key is a field name and value is a violation message
     *
     * @return FormValidationViolationDto[] | string[]
     */
    public function getFormValidationViolations(bool $asArrays = false): array
    {
        if( $asArrays ){
            $allViolations = [];
            foreach($this->formValidationViolations as $violation){
                $allViolations[$violation->getFieldName()] = $violation->getViolationMessage();
            }

            return $allViolations;
        }

        return $this->formValidationViolations;
    }

    /**
     * @param FormValidationViolationDto[] $formValidationViolations
     */
    public function setFormValidationViolations(array $formValidationViolations): void
    {
        $this->formValidationViolations = $formValidationViolations;
    }

    /**
     * FormValidationException constructor.
     * @param $message
     * @param int $code
     * @param null $previous
     */
    public function __construct($message, $code = Response::HTTP_BAD_REQUEST, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}