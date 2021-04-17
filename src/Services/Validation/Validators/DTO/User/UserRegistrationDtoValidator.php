<?php

namespace App\Services\Validation\Validators\DTO\User;

use App\Controller\Core\Application;
use App\Services\Validation\Validators\Interfaces\ValidatorInterface;
use App\DTO\User\UserRegistrationDTO;
use App\VO\Validators\ValidationResultVO;

/**
 * Handles validation of the @see UserRegistrationDTO
 *
 * Class UserRegistrationDtoValidator
 * @package App\Controller\Validators\DTO\User
 */
class UserRegistrationDtoValidator implements ValidatorInterface
{

    /**
     * @var Application $app
     */
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param object $userRegistrationDto
     * @return ValidationResultVO
     */
    public function doValidate(object $userRegistrationDto): ValidationResultVO
    {
        $validationResultVO = new ValidationResultVO;
        $validationResultVO->setValidatedClass(UserRegistrationDTO::class);

        if( !($userRegistrationDto instanceof UserRegistrationDTO) ){
            $validationResultVO->setValidable(false);

            return $validationResultVO;
        }

        $validationViolations = [];

        if( empty($userRegistrationDto->getUsername()) ){
            $validationViolations[UserRegistrationDTO::FIELD_USERNAME] = $this->app->translator->translate('validators.UserRegisterTypeValidator.username.isEmpty');
        }else {
            $user = $this->app->repositories->userRepository->findOneByName($userRegistrationDto->getUsername());
            if( !empty($user) ){
                $validationViolations[UserRegistrationDTO::FIELD_USERNAME] = $this->app->translator->translate('validators.UserRegisterTypeValidator.username.alreadyExist');
            }
        }

        if( empty($userRegistrationDto->getEmail()) ){
            $validationViolations[UserRegistrationDTO::FIELD_EMAIL] = $this->app->translator->translate('validators.UserRegisterTypeValidator.email.isEmpty');
        }elseif( !filter_var($userRegistrationDto->getEmail(), FILTER_VALIDATE_EMAIL) ){
            $validationViolations[UserRegistrationDTO::FIELD_EMAIL] = $this->app->translator->translate('validators.UserRegisterTypeValidator.email.invalidSyntax');
        }else{
            $userFoundByEmail = $this->app->repositories->userRepository->findOneByEmail($userRegistrationDto->getEmail());

            if( !empty($userFoundByEmail) ){
                $validationViolations[UserRegistrationDTO::FIELD_EMAIL] = $this->app->translator->translate('validators.UserRegisterTypeValidator.email.alreadyInUse');
            }
        }

        if( empty($userRegistrationDto->getPassword()) ){
            $validationViolations[UserRegistrationDTO::FIELD_PASSWORD] = $this->app->translator->translate('validators.UserRegisterTypeValidator.password.isEmpty');
        }

        if( $userRegistrationDto->getPassword() !== $userRegistrationDto->getPasswordRepeat() ){
            $validationViolations[UserRegistrationDTO::FIELD_PASSWORD_REPEAT] = $this->app->translator->translate('validators.UserRegisterTypeValidator.repeatPassword.doesNotMatch');
        }

        if( empty($userRegistrationDto->getLockPassword()) ){
            $validationViolations[UserRegistrationDTO::FIELD_LOCK_PASSWORD] = $this->app->translator->translate('validators.UserRegisterTypeValidator.lockPassword.isEmpty');
        }

        if( $userRegistrationDto->getLockPassword() !== $userRegistrationDto->getLockPasswordRepeat() ){
            $validationViolations[UserRegistrationDTO::FIELD_LOCK_PASSWORD_REPEAT] = $this->app->translator->translate('validators.UserRegisterTypeValidator.repeatLockPassword.doesNotMatch');
        }

        $isValid = empty($validationViolations);

        $validationResultVO->setInvalidFieldsMessages($validationViolations);
        $validationResultVO->setValid($isValid);

        return $validationResultVO;
    }

}