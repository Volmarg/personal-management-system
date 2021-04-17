<?php


namespace App\Services\Validation\Validators\Modules\Job;

use App\Services\Validation\Validators\AbstractValidator;
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
     * @var MyJobHolidaysPoolRepository $jobHolidaysPoolRepository
     */
    private $jobHolidaysPoolRepository = null;

    /**
     * @var MyJobHolidaysRepository $jobHolidaysRepository
     */
    private $jobHolidaysRepository = null;

    /**
     * Initialize logic
     *  must be overwritten by children
     */
    protected function init(): void {
        $this->jobHolidaysPoolRepository = $this->em->getRepository(MyJobHolidaysPool::class);
        $this->jobHolidaysRepository     = $this->em->getRepository(MyJobHolidays::class);
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
     * @param ValidateEntityInterface|MyJobHolidays $entity
     * @return ValidationResultVO
     * @throws Exception
     */
    public function validate(ValidateEntityInterface $entity): ValidationResultVO {

        $this->setSupportedClass(MyJobHolidays::class);
        parent::validate($entity);

        $this->validateDaysSpent($entity);
        $validationResult = $this->processValidationResult();

        return $validationResult;
    }

    /**
     * @param ValidateEntityInterface|MyJobHolidays $entity
     * @throws DBALException
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    private function validateDaysSpent($entity): void
    {
        $poolYear        = $entity->getYear();
        $daysLeftForYear = $this->jobHolidaysPoolRepository->getDaysInPoolLeftForYear($poolYear);
        $isNewEntity     = empty($entity->getId());

        if( $isNewEntity ){
            $entityDaysSpent = $entity->getDaysSpent();
        }else{
            $entityDaysSpentAfterUpdate  = $entity->getDaysSpent();

            $entityBeforeUpdate          = $this->jobHolidaysRepository->findOneEntityByIdOrNull($entity->getId(), true);
            $entityDaysSpentBeforeUpdate = $entityBeforeUpdate->getDaysSpent();

            // Entity was refreshed to get data from before update so this need be set again as doctrine sees now entity before update in memory
            $entity->setDaysSpent($entityDaysSpentAfterUpdate);

            $daysLeftForYear += $entityDaysSpentBeforeUpdate;
            $entityDaysSpent   = $entityDaysSpentAfterUpdate;
        }

        $this->constraintViolationsLists[MyJobHolidays::FIELD_DAYS_SPENT] = $this->validator->validate($entityDaysSpent, [
            new Assert\GreaterThan([
                "value"   => 0,
                "message" => $this->translator->translate('validations.myJobHolidaysValidator.greaterThan', ["%value%" => 0])
            ]),
            new Assert\LessThanOrEqual([
                "value"   => $daysLeftForYear,
                "message" => $this->translator->translate('validations.myJobHolidaysValidator.lessThanOrEqual', ["%daysLeft%" => $daysLeftForYear])
            ])
        ]);
    }

}