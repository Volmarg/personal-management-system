<?php

namespace App\Controller\Validators\Entities;

use App\Controller\Core\Repositories;
use App\Entity\Interfaces\ValidateEntityForCreateInterface;
use App\Entity\Interfaces\ValidateEntityForUpdateInterface;
use App\Entity\Interfaces\ValidateEntityInterface;
use App\Entity\Modules\Job\MyJobHolidays;
use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use App\Services\Core\Translator;
use App\VO\Validators\ValidationResultVO;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EntityValidator extends AbstractController {

    const ACTION_UPDATE = "update";
    const ACTION_CREATE = "create";

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

    /**
     * @var Translator $translator
     */
    private $translator;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, Translator $translator) {

        $this->translator = $translator;
        $this->logger     = $logger;
        $this->em         = $em;
    }

    /**
     * Entities do not share common parent/interface so no such can be provided
     * @param $entity
     * @param string $action
     * @return ValidationResultVO
     * @throws Exception
     */
    public function handleValidation($entity, string $action): ValidationResultVO
    {
        $is_validable = $this->isValidable($entity);

        $validation_result = new ValidationResultVO();
        $validation_result->setValidable(true);

        // entity is not validable so return results as valid, to omit further validation failure logic
        if( !$is_validable ){
            $validation_result->setValidable(false);
            $validation_result->setValid(true);
            return $validation_result;
        }


        switch( $action ){

            case self::ACTION_UPDATE:
                {
                    if( $entity instanceof ValidateEntityForUpdateInterface ){
                        $validation_result = $this->validate($entity);
                    }
                }
            break;

            case self::ACTION_CREATE:
                {
                    if( $entity instanceof ValidateEntityForCreateInterface ){
                        $validation_result = $this->validate($entity);
                    }
                }
            break;

            default:
            $this->logger->critical("logs.validators.undefinedActionOrInterfaceForEntityValidationIsMissing");
        }

        return $validation_result;
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
            $var_type = gettype($object);
            $message  = $this->translator->translate('logs.validators.providedVariableIsNotAnObject');
            $this->logger->critical($message . $var_type);

            throw new Exception($message);
        }

        $object_class = get_class($object);

        if( !Repositories::isEntity($object) ){
            $message = $this->translator->translate('logs.validators.objectOfGivenClassIsNotEntity');
            $this->logger->critical($message . $object_class);

            throw new Exception($message);
        }

        if( !array_key_exists($object_class, self::MAP_ENTITY_TO_VALIDATOR) ){
            $message = $this->translator->translate('logs.validators.thereIsNoValidationLogicForThisEntity');

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
        $entity_class       = get_class($entity);
        $validator_class    = self::MAP_ENTITY_TO_VALIDATOR[$entity_class];
        $validator_instance = new $validator_class($this->em, $this->translator);

        return $validator_instance;
    }

    /**
     * @param ValidateEntityInterface $entity
     * @return ValidationResultVO
     * @throws Exception
     */
    private function validate(ValidateEntityInterface $entity): ValidationResultVO
    {
        $validation_result = new ValidationResultVO();
        $validation_result->setValidable(true);

        $validator = $this->loadValidator($entity);

        $validation_result = $validator->validate($entity);
        return $validation_result;
    }

}