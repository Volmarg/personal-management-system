<?php

namespace App\Services\Validation;

use App\VO\Validators\ValidationResultVO;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var ValidatorInterface $validator
     */
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    // todo: Use ValidationResultVO instead - remove that dto

    /**
     * Validates the object and returns the array of violations
     *
     * @param object $object
     * @return ValidationResultVO
     */
    public function validateAndReturnValidationResultDto(object $object): ValidationResultVO
    {
        $violations          = $this->validator->validate($object);
        $validationResultVo = $this->checkConstraintViolationsAndReturnValidationResultVo($violations);

        return $validationResultVo;
    }

    /**
     * Will check violations array and creates the ValidationResultDTO
     *
     * @param ConstraintViolationList $violations
     * @return ValidationResultVO
     */
    public function checkConstraintViolationsAndReturnValidationResultVo(ConstraintViolationList $violations): ValidationResultVO
    {
        $validationResultVo    = new ValidationResultVO();
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