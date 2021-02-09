<?php

namespace App\Repository\Modules\Job;

use App\Entity\Modules\Job\MyJobHolidays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyJobHolidays|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyJobHolidays|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyJobHolidays[]    findAll()
 * @method MyJobHolidays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyJobHolidaysRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyJobHolidays::class);
    }

    /**
     * @param int $id
     * @param bool $forceFetch - if true then will clear the cached result and get the data from DB
     * @return MyJobHolidays|null
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    public function findOneEntityByIdOrNull(int $id, bool $forceFetch = false):? MyJobHolidays
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("jb")
            ->from(MyJobHolidays::class, "jb")
            ->where('jb.id = :id')
            ->andWhere("jb.deleted = 0")
            ->setParameter("id", $id);

        $query  = $qb->getQuery();
        $result = $query->getOneOrNullResult();

        if( $forceFetch && !empty($result) )
        {
            $this->_em->refresh($result);
        }

        return $result;
    }

    /**
     * Returns all not deleted entities
     *
     * @return MyJobHolidays[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->findBy(['deleted' => 0]);
    }
}
