<?php


namespace App\Services\Validation\Validators;

use App\DTO\ValidationResultDto;
use App\Entity\Interfaces\ValidateEntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractValidator
 * @package App\Entity\Validators
 */
abstract class AbstractValidator {

    /**
     * @var bool $updateValidation
     */
    private $updateValidation = false;

    /**
     * @var bool $createValidation
     */
    private $createValidation = false;

    /**
     * @var string $supportedClass
     */
    protected $supportedClass = "";

    /**
     * @var EntityManagerInterface $em
     */
    protected $em;

    /**
     * @var ValidatorInterface $validator
     */
    protected $validator;

    /**
     * @var array $constraintViolationsLists
     */
    protected $constraintViolationsLists = [];

    public function __construct(
        EntityManagerInterface $em,
        protected readonly TranslatorInterface $translator
    ) {
        $this->validator  = Validation::createValidator();
        $this->em         = $em;
    }

    /**
     * @return bool
     */
    public function isUpdateValidation(): bool {
        return $this->updateValidation;
    }

    /**
     * @return bool
     */
    public function isCreateValidation(): bool {
        return $this->createValidation;
    }

    /**
     * @return string
     */
    protected function getSupportedClass(): string {
        return $this->supportedClass;
    }

    /**
     * Initialize logic
     *  must be overwritten by children
     */
    protected abstract function init(): void;

    /**
     * Set class for which validation is supported
     *  must be overwritten in every child
     * @param string $supportedClass
     * @throws Exception
     */
    protected function setSupportedClass(string $supportedClass): void {
        throw new Exception("You must overwrite this method in child and set supported class property there");
    }

    /**
     * Validate given entity with set of rules
     *  must be extended by children
     *
     * @param ValidateEntityInterface $entity
     *
     * @return ValidationResultDto
     * @throws Exception
     */
    public function validate(ValidateEntityInterface $entity): ValidationResultDto
    {
        $this->init();

        $entityClass = get_class($entity);

        if( $entityClass !== $this->getSupportedClass() ){
            throw new Exception('This entity is not supported for in this validator');
        }

        $validationResult = new ValidationResultDto();
        return $validationResult;
    }

    /**
     * Will check if there are any violations and will add fields/messages to the result
     * @return ValidationResultDto
     */
    protected function processValidationResult(): ValidationResultDto
    {
        $validationResult = new ValidationResultDto();
        $validationResult->setValidable(true);

        $violatedFieldsWithMessages = [];

        if( empty($this->constraintViolationsLists) ){
            $validationResult->setValid(true);
            return $validationResult;
        }

        foreach($this->constraintViolationsLists as $fieldName => $constraintViolationList ){

            /**
             * @var ConstraintViolation $violation
             */
            foreach( $constraintViolationList as $constraintViolation ){
                $violationMessage                       = $constraintViolation->getMessage();
                $violatedFieldsWithMessages[$fieldName] = $violationMessage;
            }
        }

        $validationResult->setInvalidFieldsMessages($violatedFieldsWithMessages);

        if( empty($violatedFieldsWithMessages) ){
            $validationResult->setValid(true);
        }

        return $validationResult;
    }
}