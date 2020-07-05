<?php


namespace App\Controller\Validators\Entities;

use App\Entity\Interfaces\ValidateEntityInterface;
use App\Entity\Modules\Job\MyJobHolidays;
use App\Entity\Modules\Job\MyJobHolidaysPool;
use App\Repository\Modules\Job\MyJobHolidaysPoolRepository;
use App\Repository\Modules\Job\MyJobHolidaysRepository;
use App\VO\Validators\ValidationResultVO;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

class MyJobHolidaysValidator extends AbstractValidator {

    /**
     * @var MyJobHolidaysPoolRepository $job_holidays_pool_repository
     */
    private $job_holidays_pool_repository = null;

    /**
     * @var MyJobHolidaysRepository $job_holidays_repository
     */
    private $job_holidays_repository = null;

    /**
     * Initialize logic
     *  must be overwritten by children
     */
    protected function init(): void {
        $this->job_holidays_pool_repository = $this->em->getRepository(MyJobHolidaysPool::class);
        $this->job_holidays_repository      = $this->em->getRepository(MyJobHolidays::class);
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
     * @param ValidateEntityInterface|MyJobHolidays $entity
     * @return ValidationResultVO
     * @throws Exception
     */
    public function validate(ValidateEntityInterface $entity): ValidationResultVO {

        $this->setSupportedClass(MyJobHolidays::class);
        parent::validate($entity);

        $this->validateDaysSpent($entity);
        $validation_result = $this->processValidationResult();

        return $validation_result;
    }

    /**
     * @param ValidateEntityInterface|MyJobHolidays $entity
     * @throws DBALException
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    private function validateDaysSpent($entity): void
    {
        $pool_year          = $entity->getYear();
        $days_left_for_year = $this->job_holidays_pool_repository->getDaysInPoolLeftForYear($pool_year);
        $is_new_entity      = empty($entity->getId());

        if( $is_new_entity ){
            $entity_days_spent = $entity->getDaysSpent();
        }else{
            $entity_days_spent_after_update  = $entity->getDaysSpent();

            $entity_before_update            = $this->job_holidays_repository->findOneEntityByIdOrNull($entity->getId(), true);
            $entity_days_spent_before_update = $entity_before_update->getDaysSpent();

            // Entity was refreshed to get data from before update so this need be set again as doctrine sees now entity before update in memory
            $entity->setDaysSpent($entity_days_spent_after_update);

            $days_left_for_year += $entity_days_spent_before_update;
            $entity_days_spent   = $entity_days_spent_after_update;
        }

        $this->constraint_violations_lists[MyJobHolidays::FIELD_DAYS_SPENT] = $this->validator->validate($entity_days_spent, [
            new Assert\GreaterThan([
                "value"   => 0,
                "message" => $this->translator->translate('validations.myJobHolidaysValidator.greaterThan', ["%value%" => 0])
            ]),
            new Assert\LessThanOrEqual([
                "value"   => $days_left_for_year,
                "message" => $this->translator->translate('validations.myJobHolidaysValidator.lessThanOrEqual', ["%daysLeft%" => $days_left_for_year])
            ])
        ]);
    }

}