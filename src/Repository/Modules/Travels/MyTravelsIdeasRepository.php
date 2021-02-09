<?php

namespace App\Repository\Modules\Travels;

use App\Entity\Modules\Travels\MyTravelsIdeas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyTravelsIdeas|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyTravelsIdeas|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyTravelsIdeas[]    findAll()
 * @method MyTravelsIdeas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyTravelsIdeasRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyTravelsIdeas::class);
    }

    /**
     * @param bool $includeEmpty
     * @return array
     * @throws DBALException
     */
    public function getAllCategories(bool $includeEmpty = false) {
        $categories = [];
        $connection = $this->getEntityManager()->getConnection();

        $deletedStatuses = [0];
        if( $includeEmpty ){
            $deletedStatuses[] = 1;
        }

        $sql = '
            SELECT mc.category
            FROM my_travel_idea mc

            JOIN my_travel_idea mci
            ON mci.id = mc.id

            WHERE mc.category IS NOT NULL
            AND mci.deleted IN (?)
            GROUP BY mc.category
        ';

        $params = [
            $deletedStatuses
        ];

        $types = [
            Connection::PARAM_STR_ARRAY,
        ];

        $statement = $connection->executeQuery($sql, $params, $types);
        $results   = $statement->fetchAll();

        if (!empty($results)) {
            foreach ($results as $result) {
                foreach ($result as $key => $value) {
                    $categories[$value] = $value;
                }
            }
        }

        return $categories;
    }

    /**
     * Will return one entity for id, if none is found then null is returned
     *
     * @param int $id
     * @return MyTravelsIdeas|null
     */
    public function findOneById(int $id): ?MyTravelsIdeas
    {
        return $this->find($id);
    }

    /**
     * Will return all not deleted entities
     *
     * @return MyTravelsIdeas[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->findBy(['deleted' => 0]);
    }

    /**
     * Will save the entity in the database
     *
     * @param MyTravelsIdeas $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(MyTravelsIdeas $entity): void
    {
        $this->_em->persist($entity);;
        $this->_em->flush();;
    }
}
