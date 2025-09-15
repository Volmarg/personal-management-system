<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPaymentsSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsSettings[]    findAll()
 * @method MyPaymentsSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsSettingsRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyPaymentsSettings::class);
    }

    public function fetchCurrencyMultiplier() {
        $result = $this->findBy(['name' => 'currency_multiplier']);
        return (empty($result) ? null : $result[0]->getValue());
    }

    /**
     * @return MyPaymentsSettings[]
     */
    public function getAllPaymentsTypes(): array {
        return $this->findBy([
            'name'    => 'type',
            'deleted' => 0
        ]);
    }

    /**
     * @param int $id
     *
     * @return MyPaymentsSettings|null
     */
    public function findPaymentType(int $id): ?MyPaymentsSettings
    {
        return $this->findOneBy([
            'id'      => $id,
            'name'    => 'type',
            'deleted' => 0,
        ]);
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsSettings|null
     */
    public function findOneById(int $id): ?MyPaymentsSettings
    {
       return $this->find($id);
    }

}
