<?php


namespace App\Services\Validation\Validators\Modules\Payments;

use App\Services\Validation\Validators\AbstractValidator;
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
     * @param string $supportedClass
     */
    protected function setSupportedClass(string $supportedClass): void {
        $this->supportedClass = $supportedClass;
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
        $validationResult = $this->processValidationResult();

        return $validationResult;
    }

    /**
     * @param ValidateEntityInterface|MyRecurringPaymentMonthly $entity
     */
    private function validateDateOfMonth($entity): void
    {
        $this->constraintViolationsLists[MyRecurringPaymentMonthly::FIELD_DAYS_OF_MONTH] = $this->validator->validate($entity->getDayOfMonth(), [
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