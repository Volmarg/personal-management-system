<?php

namespace App\Controller\Validators;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

abstract class AbstractValidator {

    /**
     * @var Validation
     */
    private $validator;

    public function __construct() {
        $this->validator = Validation::createValidator();
    }

    /**
     * This will check if Violations list is empty,
     * this must be handled this way as Validator returns also empty array (single element) if no violation was present
     * @param ConstraintViolationListInterface $violation_list
     * @return bool
     */
    public static function areViolations(ConstraintViolationListInterface $violation_list): bool
    {
        $all_violations = [];

        foreach($violation_list as $violation ){
            if( !empty($violation) ){
                $all_violations[] = $violation;
            }
        }

        if( empty($all_violations) ){
            return true;
        }

        return false;
    }

}