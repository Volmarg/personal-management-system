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
        $form_validation_violations = [];

        $username             = $form_data[UserRegisterType::FIELD_USERNAME];
        $email                = $form_data[UserRegisterType::FIELD_EMAIL];
        $password             = $form_data[UserRegisterType::FIELD_PASSWORD];
        $password_repeat      = $form_data[UserRegisterType::FIELD_PASSWORD_REPEAT];
        $lock_password        = $form_data[UserRegisterType::FIELD_LOCK_PASSWORD];
        $lock_password_repeat = $form_data[UserRegisterType::FIELD_LOCK_PASSWORD_REPEAT];

        if( empty($username) ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.username.isEmpty');
            $form_validation_violations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_USERNAME, $message);
        }else {
            $user = $this->controllers->getUserController()->findOneByName($username);
            if( !empty($user) ){
                $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.username.alreadyExist');
                $form_validation_violations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_USERNAME, $message);
            }
        }

        if( empty($email) ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.email.isEmpty');
            $form_validation_violations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_EMAIL, $message);
        }elseif( !filter_var($email, FILTER_VALIDATE_EMAIL) ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.email.invalidSyntax');
            $form_validation_violations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_EMAIL, $message);
        }else{
            $user_found_by_email = $this->controllers->getUserController()->findOneByEmail($email);

            if( !empty($user_found_by_email) ){
                $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.email.alreadyInUse');
                $form_validation_violations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_EMAIL, $message);
            }
        }


        if( empty($password) ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.password.isEmpty');
            $form_validation_violations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_PASSWORD, $message);
        }

        if( $password !== $password_repeat ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.repeatPassword.doesNotMatch');
            $form_validation_violations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_PASSWORD_REPEAT, $message);
        }

        if( empty($lock_password) ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.lockPassword.isEmpty');
            $form_validation_violations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_LOCK_PASSWORD, $message);
        }

        if( $lock_password !== $lock_password_repeat ){
            $message                      = $this->app->translator->translate('validators.UserRegisterTypeValidator.repeatLockPassword.doesNotMatch');
            $form_validation_violations[] = FormValidationViolationDto::buildForFieldNameAndMessage(UserRegisterType::FIELD_LOCK_PASSWORD_REPEAT, $message);
        }

        if( !empty($form_validation_violations) ){
            $form_validation_exception =  new FormValidationException("Invalid form data has been provided");
            $form_validation_exception->setFormValidationViolations($form_validation_violations);
            throw $form_validation_exception;
        }
    }
}
