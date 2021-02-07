<?php


namespace App\Controller\Validators\Entities;

use App\Entity\Interfaces\ValidateEntityInterface;
use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use App\VO\Validators\ValidationResultVO;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MyRecurringPaymentsValidator
 * @package App\Controller\Validators\Entities
 */
class MyRecurringPaymentsValidator extends AbstractValidator {

    /**
     * Initialize logic
     *  must be overwritten by children
     */
    protected function init(): void {
        // nothing here
    }

    /**
     * @inheritDoc
     * @param string $supported_class
     */
    protected function setSupportedClass(string $supported_class): void {
        $this->supported_class = $supported_class;
    }

    /**
     * @inheritDoc
     * @param ValidateEntityInterface|MyRecurringPaymentMonthly $entity
     * @return ValidationResultVO
     * @throws Exception
     */
    public function validate(ValidateEntityInterface $entity): ValidationResultVO {

        $this->setSupportedClass(MyRecurringPaymentMonthly::class);
        parent::validate($entity);

        $this->validateDateOfMonth($entity);
        $validation_result = $this->processValidationResult();

        return $validation_result;
    }

    /**
     * @param ValidateEntityInterface|MyRecurringPaymentMonthly $entity
     */
    private function validateDateOfMonth($entity): void
    {
        $this->constraint_violations_lists[MyRecurringPaymentMonthly::FIELD_DAYS_OF_MONTH] = $this->validator->validate($entity->getDayOfMonth(), [
            new Assert\GreaterThanOrEqual([
                "value"   => MyRecurringPaymentMonthly::MIN_DAY_OF_MONTH,
                "message" => $this->translator->translate('validations.myRecurringPaymentsValidator.dayOfMonth.greaterThanOrEqual', ["%value%" => MyRecurringPaymentMonthly::MIN_DAY_OF_MONTH])
            ]),
            new Assert\LessThanOrEqual([
                "value"   => MyRecurringPaymentMonthly::MAX_DAY_OF_MONTH,
                "message" => $this->translator->translate('validations.myRecurringPaymentsValidator.dayOfMonth.lessThanOrEqual', ["%value%" => MyRecurringPaymentMonthly::MAX_DAY_OF_MONTH])
            ])
        ]);
    }

}