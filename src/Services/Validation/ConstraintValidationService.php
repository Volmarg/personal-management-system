<?php

namespace App\Services\Validation;

use App\DTO\ValidationResultDto;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Standard validator which can be used to validate if object constraints/assertions are correct
 * - annotation based,
 * - simple array of constraint violations,
 * - constraint provided in method,
 *
 * Class ConstraintValidationService
 * @package App\Services\Validation
 */
class ConstraintValidationService
{
    /**
     * Will check violations array and creates the ValidationResultDTO
     *
     * @param ConstraintViolationList $violations
     *
     * @return ValidationResultDto
     */
    public function checkConstraintViolationsAndReturnValidationResultVo(ConstraintViolationList $violations
    ): ValidationResultDto
    {
        $validationResultVo     = new ValidationResultDto();
        $violationsWithMessages = [];

        /**@var $constraintViolation ConstraintViolation*/
        foreach($violations as $constraintViolation){
            $violationsWithMessages[$constraintViolation->getPropertyPath()] = $constraintViolation->getMessage();
        }

        $validationResultVo->setValid(true);
        if( !empty($violationsWithMessages) ){
            $validationResultVo->setValid(false);
            $validationResultVo->setInvalidFieldsMessages($violationsWithMessages);
        }

        return $validationResultVo;
    }

}