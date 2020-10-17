<?php

namespace App\Form\Interfaces;

use App\Services\Exceptions\FormValidationException;

interface FormValidatorInterface
{

    /**
     * @throws FormValidationException
     * @param array $form_data
     */
    public function doValidate(array $form_data): void;

}