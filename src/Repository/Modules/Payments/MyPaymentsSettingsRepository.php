<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyPaymentsSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsSettings[]    findAll()
 * @method MyPaymentsSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsSettingsRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyPaymentsSettings::class);
    }

    public function fetchCurrencyMultiplier() {
        $result = $this->findBy(['name' => 'currency_multiplier']);
        return (empty($result) ? null : $result[0]->getValue());
    }

    public function fetchCurrencyMultiplierRecord() {
        return $this->findBy(['name' => 'currency_multiplier']);
    }

    public function getAllPaymentsTypes() {
        return $this->findBy([
            'name'    => 'type',
            'deleted' => 0
        ]);
    }

}
