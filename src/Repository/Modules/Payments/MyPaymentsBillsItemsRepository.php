<?php

namespace App\Repository\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsBillsItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyPaymentsBillsItems|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyPaymentsBillsItems|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyPaymentsBillsItems[]    findAll()
 * @method MyPaymentsBillsItems[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyPaymentsBillsItemsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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

}
