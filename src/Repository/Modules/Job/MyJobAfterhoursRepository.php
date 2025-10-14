<?php

namespace App\Repository\Modules\Job;

use App\Entity\Modules\Job\MyJobAfterhours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyJobAfterhours|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyJobAfterhours|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyJobAfterhours[]    findAll()
 * @method MyJobAfterhours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyJobAfterhoursRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyJobAfterhours::class);
    }

    public function getAllGoalsNames(){

        $sql = "
            SELECT DISTINCT goal
            FROM my_job_afterhour
            WHERE 1
                AND goal IS NOT NULL;
        ";

        $connection = $this->getEntityManager()->getConnection();
        $statement  = $connection->prepare($sql);

        $statement->execute();
        $results = $statement->fetchAll();

        return (!empty($results) ? array_column($results,'goal') : []);
    }

    /**
     * Will search for not deleted afterhours by types
     *
     * @param string[] $types
     * @return MyJobAfterhours[]
     */
    public function findAllNotDeletedByType(array $types): array
    {
        $entities = $this->findBy([
            "deleted" => 0,
            "Type"    => $types
        ], ["Date" => "DESC"]);

        return $entities;
    }

    /**
     * Will return one entity for given id, if such does not exist then null will be returned
     *
     * @param int $id
     * @return MyJobAfterhours|null
     */
    public function findOneById(int $id): ?MyJobAfterhours
    {
        return $this->find($id);
    }

}
