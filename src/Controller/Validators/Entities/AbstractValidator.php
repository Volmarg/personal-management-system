<?php


namespace App\Controller\Validators\Entities;

use App\Entity\Interfaces\ValidateEntityInterface;
use App\Services\Core\Translator;
use App\VO\Validators\ValidationResultVO;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AbstractValidator
 * @package App\Entity\Validators
 */
abstract class AbstractValidator {

    /**
     * @var bool $update_validation
     */
    private $update_validation = false;

    /**
     * @var bool $create_validation
     */
    private $create_validation = false;

    /**
     * @var string $supported_class
     */
    protected $supported_class = "";

    /**
     * @var EntityManagerInterface $em
     */
    protected $em;

    /**
     * @var ValidatorInterface $validator
     */
    protected $validator;

    /**
     * @var Translator $translator
     */
    protected $translator;

    /**
     * @var array $constraint_violations_lists
     */
    protected $constraint_violations_lists = [];

    public function __construct(EntityManagerInterface $em, Translator $translator) {
        $this->translator = $translator;
        $this->validator  = Validation::createValidator();
        $this->em         = $em;
    }

    /**
     * @return bool
     */
    public function isUpdateValidation(): bool {
        return $this->update_validation;
    }

    /**
     * @return bool
     */
    public function isCreateValidation(): bool {
        return $this->create_validation;
    }

    /**
     * @return string
     */
    protected function getSupportedClass(): string {
        return $this->supported_class;
    }

    /**
     * Initialize logic
     *  must be overwritten by children
     */
    protected abstract function init(): void;

    /**
     * Set class for which validation is supported
     *  must be overwritten in every child
     * @param string $supported_class
     * @throws Exception
     */
    protected function setSupportedClass(string $supported_class): void {
        throw new Exception("You must overwrite this method in child and set supported class property there");
    }

    /**
     * Validate given entity with set of rules
     *  must be extended by children
     * @param ValidateEntityInterface $entity
     * @return ValidationResultVO
     * @throws Exception
     */
    public function validate(ValidateEntityInterface $entity): ValidationResultVO
    {
        $this->init();

        $entity_class = get_class($entity);

        if( $entity_class !== $this->getSupportedClass() ){
            throw new Exception('This entity is not supported for in this validator');
        }

        $validation_result = new ValidationResultVO();
        return $validation_result;
    }

    /**
     * Will check if there are any violations and will add fields/messages to the result
     * @return ValidationResultVO
     */
    protected function processValidationResult(): ValidationResultVO
    {
        $validation_result = new ValidationResultVO();
        $validation_result->setValidable(true);

        $violated_fields_with_messages = [];

        if( empty($this->constraint_violations_lists) ){
            $validation_result->setValid(true);
            return $validation_result;
        }

        foreach($this->constraint_violations_lists as $field_name => $constraint_violation_list ){

            /**
             * @var ConstraintViolation $violation
             */
            foreach( $constraint_violation_list as $constraint_violation ){
                $violation_message                          = $constraint_violation->getMessage();
                $violated_fields_with_messages[$field_name] = $violation_message;
            }
        }

        $validation_result->setInvalidFieldsMessages($violated_fields_with_messages);

        if( empty($violated_fields_with_messages) ){
            $validation_result->setValid(true);
        }

        return $validation_result;
    }
}