<?php


namespace App\Form\Events;


use App\Form\Interfaces\FormValidatorInterface;
use App\Services\Exceptions\FormValidationException;
use Symfony\Component\Form\FormEvent;

class ValidateForm
{
    /**
     * @param FormEvent $event
     * @param FormValidatorInterface $formValidator
     * @throws FormValidationException
     */
    public static function onSubmit(FormEvent $event, FormValidatorInterface $formValidator)
    {
        $formData = $event->getData();
        $formValidator->doValidate($formData);
    }

}