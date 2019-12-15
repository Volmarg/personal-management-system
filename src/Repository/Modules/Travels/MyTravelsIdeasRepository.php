<?php

namespace App\Repository\Modules\Travels;

use App\Entity\Modules\Travels\MyTravelsIdeas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyTravelsIdeas|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyTravelsIdeas|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyTravelsIdeas[]    findAll()
 * @method MyTravelsIdeas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyTravelsIdeasRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyTravelsIdeas::class);
    }

    public function getAllCategories() {
        $categories = [];
        $connection = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT category
            FROM my_travel_idea
            WHERE category IS NOT NULL
        ';

        $statement = $connection->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();

        if (!empty($results)) {
            foreach ($results as $result) {
                foreach ($result as $key => $value) {
                    $categories[$value] = $value;
                }
            }
        }

        return $categories;
    }
}
