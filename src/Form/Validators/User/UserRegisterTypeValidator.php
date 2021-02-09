<?php

namespace App\Form\Validators\User;

use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\DTO\Forms\FormValidationViolationDto;
use App\Form\Interfaces\FormValidatorInterface;
use App\Form\User\UserRegisterType;
use App\Form\Validators\FormTypeValidator;
use App\Services\Exceptions\FormValidationException;

class UserRegisterTypeValidator extends FormTypeValidator implements FormValidatorInterface
{
    public function __construct(Application $app, Controllers $controllers)
    {
        parent::__construct($app, $controllers);
    }

    /**
     * @throws FormValidationException
     * @param array $form_data
     */
    public function doValidate(array $form_data): void
    {
        $formValidationViolations = [];

        $username           = $form_data[UserRegisterType::FIELD_USERNAME];
        $email              = $form_data[UserRegisterType::FIELD_EMAIL];
        $password           = $form_data[UserRegisterType::FIELD_PASSWORD];
        $passwordRepeat     = $form_data[UserRegisterType::FIELD_PASSWORD_REPEAT];
        $lockPassword       = $form_data[UserRegisterType::FIELD_LOCK_PASSWORD];
        $lockPasswordRepeat = $form_data[UserRegisterType::FIELD_LOCK_PASSWORD_REPEAT];

        if( empty($username) ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.username.isEmpty');
            $formValidationViolations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_USERNAME, $message);
        }else {
            $user = $this->controllers->getUserController()->findOneByName($username);
            if( !empty($user) ){
                $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.username.alreadyExist');
                $formValidationViolations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_USERNAME, $message);
            }
        }

        if( empty($email) ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.email.isEmpty');
            $formValidationViolations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_EMAIL, $message);
        }elseif( !filter_var($email, FILTER_VALIDATE_EMAIL) ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.email.invalidSyntax');
            $formValidationViolations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_EMAIL, $message);
        }else{
            $userFoundByEmail = $this->controllers->getUserController()->findOneByEmail($email);

            if( !empty($userFoundByEmail) ){
                $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.email.alreadyInUse');
                $formValidationViolations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_EMAIL, $message);
            }
        }

        if( empty($password) ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.password.isEmpty');
            $formValidationViolations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_PASSWORD, $message);
        }

        if( $password !== $passwordRepeat ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.repeatPassword.doesNotMatch');
            $formValidationViolations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_PASSWORD_REPEAT, $message);
        }

        if( empty($lockPassword) ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.lockPassword.isEmpty');
            $formValidationViolations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_LOCK_PASSWORD, $message);
        }

        if( $lockPassword !== $lockPasswordRepeat ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.repeatLockPassword.doesNotMatch');
            $formValidationViolations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_LOCK_PASSWORD_REPEAT, $message);
        }

        if( !empty($formValidationViolations) ){
            $formValidationException =  new FormValidationException("Invalid form data has been provided");
            $formValidationException->setFormValidationViolations($formValidationViolations);
            throw $formValidationException;
        }
    }
}
