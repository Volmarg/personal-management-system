<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsBillsItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyPaymentsBillsItems|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsBillsItems|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsBillsItems[]    findAll()
 * @method MyPaymentsBillsItems[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsBillsItemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyPaymentsBillsItems::class);
    }

    /**
     * @return MyPaymentsBillsItems[]
     */
    public function getAllNotDeleted(): array
    {
        $entities = $this->findBy([MyPaymentsBillsItems::FIELD_DELETED => 0], [MyPaymentsBillsItems::FIELD_ID => "DESC"]);
        return $entities;
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     * @return MyPaymentsBillsItems|null
     */
    public function findOneById(int $id): ?MyPaymentsBillsItems
    {
        return $this->find($id);
    }

}
