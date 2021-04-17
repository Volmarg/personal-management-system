<?php

namespace App\Services\Validation;

use App\Controller\Core\Application;
use App\Services\Validation\Validators\DTO\User\UserRegistrationDtoValidator;
use App\Services\Validation\Validators\Interfaces\ValidatorInterface;
use App\DTO\Interfaces\ValidableDtoInterface;
use App\DTO\User\UserRegistrationDTO;
use App\VO\Validators\ValidationResultVO;

/**
 * Handles validations of variety of dtos
 *
 * Class DtoValidator
 * @package App\Controller\Validators\DTO
 */
class DtoValidatorService
{

    /**
     * This is used to obtain the validator for dto, if no mapping is provided then dto will not be validated
     */
    const MAP_DTO_CLASS_TO_VALIDATOR_CLASS = [
        UserRegistrationDTO::class => UserRegistrationDtoValidator::class,
    ];

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * DtoValidator constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Will validate given dto
     */
    public function doValidate(object $dto): ?ValidationResultVO
    {
        if( !$this->isValidable($dto) ){
            return null;
        }

        $validator          = $this->getValidatorInstanceForDto($dto);
        $validationResultVo = $validator->doValidate($dto);

        return $validationResultVo;
    }

    /**
     * Will check if given dto is validable at all
     *
     * @param object $dto
     * @return bool
     */
    private function isValidable(object $dto): bool
    {
        $dtoClass = get_class($dto);

        $hasInterface     = ($dto instanceof ValidableDtoInterface);
        $isSupportedClass = array_key_exists($dtoClass, self::MAP_DTO_CLASS_TO_VALIDATOR_CLASS);

        return ($hasInterface && $isSupportedClass);
    }

    /**
     * Will return validator instance for given dto
     */
    private function getValidatorInstanceForDto(object $dto): ValidatorInterface
    {
        $dtoClass       = get_class($dto);
        $validatorClass = self::MAP_DTO_CLASS_TO_VALIDATOR_CLASS[$dtoClass];
        $validator      = new $validatorClass($this->app);

        return $validator;
    }


}