<?php

namespace App\Repository\Modules\Car;

use App\Entity\Modules\Car\MyCar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyCar|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyCar|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyCar[]    findAll()
 * @method MyCar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyCarRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyCar::class);
    }

    public function getIncomingCarSchedulesInMonths(int $months){

        $connection = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT name AS name,
              date      AS date,
              DATEDIFF(date ,NOW()) AS daysDiff
            FROM my_car
            WHERE date BETWEEN NOW() AND NOW() + INTERVAL :months MONTH
            AND DATEDIFF (date,NOW()) > 0
            AND deleted = 0
        ";

        $binded_values = [
          'months'  => $months
        ];

        $statement = $connection->executeQuery($sql, $binded_values);
        $results   = $statement->fetchAll();

        return $results;
    }

}
