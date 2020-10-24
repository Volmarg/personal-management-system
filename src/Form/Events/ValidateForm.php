<?php


namespace App\Form\Events;


use App\Form\Interfaces\FormValidatorInterface;
use App\Services\Exceptions\FormValidationException;
use Symfony\Component\Form\FormEvent;

class ValidateForm
{
    /**
     * @param FormEvent $event
     * @param FormValidatorInterface $form_validator
     * @throws FormValidationException
     */
    public static function onSubmit(FormEvent $event, FormValidatorInterface $form_validator)
    {
        $form_data = $event->getData();
        $form_validator->doValidate($form_data);
    }

}