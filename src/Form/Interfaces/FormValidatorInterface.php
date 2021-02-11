<?php

namespace App\Form\Interfaces;

use App\Services\Exceptions\FormValidationException;

interface FormValidatorInterface
{

    /**
     * @param array $formData
     * @throws FormValidationException
     */
    public function doValidate(array $formData): void;

}