<?php

namespace App\Services\Exceptions;

use App\DTO\Forms\FormValidationViolationDto;
use Symfony\Component\HttpFoundation\Response;

class FormValidationException extends \Exception
{

    /**
     * @var FormValidationViolationDto[] $form_validation_violations
     */
    private array $form_validation_violations;

    /**
     * @param bool $as_arrays
     *          - if false then returns array of dtos
     *          - if true then return array of arrays where each key is a field name and value is a violation message
     *
     * @return FormValidationViolationDto[] | string[]
     */
    public function getFormValidationViolations(bool $as_arrays = false): array
    {
        if( $as_arrays ){
            $all_violations = [];
            foreach($this->form_validation_violations as $violation){
                $all_violations[$violation->getFieldName()] = $violation->getViolationMessage();
            }

            return $all_violations;
        }

        return $this->form_validation_violations;
    }

    /**
     * @param FormValidationViolationDto[] $form_validation_violations
     */
    public function setFormValidationViolations(array $form_validation_violations): void
    {
        $this->form_validation_violations = $form_validation_violations;
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