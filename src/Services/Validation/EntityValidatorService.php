<?php

namespace App\Services\Validation;

use App\DTO\ValidationResultDto;
use App\Entity\Interfaces\ValidateEntityForCreateInterface;
use App\Entity\Interfaces\ValidateEntityForUpdateInterface;
use App\Entity\Interfaces\ValidateEntityInterface;
use App\Entity\Modules\Job\MyJobHolidays;
use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use App\Services\Database\DoctrineService;
use App\Services\Validation\Validators\AbstractValidator;
use App\Services\Validation\Validators\Modules\Job\MyJobHolidaysValidator;
use App\Services\Validation\Validators\Modules\Payments\MyRecurringPaymentsValidator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles validations of entities
 *
 * Class EntityValidatorService
 * @package App\Services\Validation
 */
class EntityValidatorService extends AbstractController {

    const ACTION_UPDATE = "update";
    const ACTION_CREATE = "create";

    /**
     * Contains mapping of entity to validator, if no validator is provided for entity in this mapping then entity is not validated
     */
    const MAP_ENTITY_TO_VALIDATOR = [
        MyJobHolidays::class             => MyJobHolidaysValidator::class,
        MyRecurringPaymentMonthly::class => MyRecurringPaymentsValidator::class,
    ];

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    public function __construct(
        EntityManagerInterface $em, 
        LoggerInterface $logger, 
        private readonly TranslatorInterface $translator
    ) {
        $this->logger     = $logger;
        $this->em         = $em;
    }

    /**
     * Entities do not share common parent/interface so no such can be provided
     *
     * @param $entity
     * @param string $action
     *
     * @return ValidationResultDto
     * @throws Exception
     */
    public function handleValidation($entity, string $action): ValidationResultDto
    {
        $isValidable = $this->isValidable($entity);

        $validationResult = new ValidationResultDto();
        $validationResult->setValidable(true);

        // entity is not validable so return results as valid, to omit further validation failure logic
        if( !$isValidable ){
            $validationResult->setValidable(false);
            $validationResult->setValid(true);
            return $validationResult;
        }


        switch( $action ){

            case self::ACTION_UPDATE:
                {
                    if( $entity instanceof ValidateEntityForUpdateInterface ){
                        $validationResult = $this->validate($entity);
                    }
                }
            break;

            case self::ACTION_CREATE:
                {
                    if( $entity instanceof ValidateEntityForCreateInterface ){
                        $validationResult = $this->validate($entity);
                    }
                }
            break;

            default:
            $this->logger->critical("logs.validators.undefinedActionOrInterfaceForEntityValidationIsMissing");
        }

        return $validationResult;
    }

    /**
     * Check if provided object can be validated as entity
     * @param $object
     * @return bool
     * @throws Exception
     */
    private function isValidable($object): bool
    {
        // not validable - don't process further
        if( !$object instanceof ValidateEntityInterface ){
            return false;
        }

        if( !is_object($object) ){
            $varType = gettype($object);
            $message = $this->translator->trans('logs.validators.providedVariableIsNotAnObject');
            $this->logger->critical($message . $varType);

            throw new Exception($message);
        }

        $objectClass = get_class($object);
        if( !DoctrineService::isEntity($object) ){
            $message = $this->translator->trans('logs.validators.objectOfGivenClassIsNotEntity');
            $this->logger->critical($message . $objectClass);

            throw new Exception($message);
        }

        if( !array_key_exists($objectClass, self::MAP_ENTITY_TO_VALIDATOR) ){
            $message = $this->translator->trans('logs.validators.thereIsNoValidationLogicForThisEntity');

            throw new Exception($message);
        }

        return true;
    }

    /**
     * Load the validator and return it's instance based on the mapping
     * @param ValidateEntityInterface $entity
     * @return AbstractValidator|null
     */
    private function loadValidator(ValidateEntityInterface $entity): AbstractValidator
    {
        $entityClass       = get_class($entity);
        $validatorClass    = self::MAP_ENTITY_TO_VALIDATOR[$entityClass];
        $validatorInstance = new $validatorClass($this->em, $this->translator);

        return $validatorInstance;
    }

    /**
     * @param ValidateEntityInterface $entity
     *
     * @return ValidationResultDto
     * @throws Exception
     */
    private function validate(ValidateEntityInterface $entity): ValidationResultDto
    {
        $validationResult = new ValidationResultDto();
        $validationResult->setValidable(true);

        $validator = $this->loadValidator($entity);

        $validationResult = $validator->validate($entity);
        return $validationResult;
    }

}