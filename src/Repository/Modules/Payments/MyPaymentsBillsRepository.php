<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsBills;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPaymentsBills|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsBills|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsBills[]    findAll()
 * @method MyPaymentsBills[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsBillsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyPaymentsBills::class);
    }

    /**
     * @return MyPaymentsBills[]
     */
    public function getAllNotDeleted(): array
    {
        $entities = $this->findBy([MyPaymentsBills::FIELD_DELETED => 0], [MyPaymentsBills::FIELD_ID => "DESC"]);
        return $entities;
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsBills|null
     */
    public function findOneById(int $id): ?MyPaymentsBills
    {
        return $this->find($id);
    }

}
