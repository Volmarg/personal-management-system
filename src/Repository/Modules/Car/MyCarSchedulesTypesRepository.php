<?php

namespace App\Repository\Modules\Car;

use App\Entity\Modules\Car\MyCarSchedulesTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyCarSchedulesTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyCarSchedulesTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyCarSchedulesTypes[]    findAll()
 * @method MyCarSchedulesTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyCarSchedulesTypesRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyCarSchedulesTypes::class);
    }

}
