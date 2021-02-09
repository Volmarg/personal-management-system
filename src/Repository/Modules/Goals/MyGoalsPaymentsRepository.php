<?php

namespace App\Repository\Modules\Goals;

use App\Entity\Modules\Goals\MyGoalsPayments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyGoalsPayments|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyGoalsPayments|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyGoalsPayments[]    findAll()
 * @method MyGoalsPayments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyGoalsPaymentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyGoalsPayments::class);
    }

    /**
     * Returns entities explicitly for dashboard
     * @return MyGoalsPayments[]
     */
    public function getGoalsPaymentsForDashboard(){
        $results = $this->findBy([
            'displayOnDashboard' => 1,
            'deleted'            => 0
        ]);
        return $results;
    }

    /**
     * Will return all not deleted entities
     *
     * @return MyGoalsPayments[]
     */
    public function getAllNotDeleted()
    {
        return $this->findBy(['deleted' => 0]);
    }

    /**
     * Will return single entity for given id, if none was found then null will be returned
     *
     * @param int $id
     * @return MyGoalsPayments|null
     */
    public function findOneById(int $id): ?MyGoalsPayments
    {
        return $this->find($id);
    }

    /**
     * Will return one entity for given name if such exist, otherwise null is returned
     *
     * @param string $name
     * @return MyGoalsPayments|null
     */
    public function getOneByName(string $name): ?MyGoalsPayments
    {
        $allEntities = $this->findBy(['name' => $name]);
        if( empty($allEntities) ){
            return null;
        }

        $firstKey = array_key_first($allEntities);

        return $allEntities[$firstKey];
    }

}
