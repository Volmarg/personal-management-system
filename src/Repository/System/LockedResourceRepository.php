<?php

namespace App\Repository\System;

use App\Entity\System\LockedResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @method LockedResource|null find($id, $lockMode = null, $lockVersion = null)
 * @method LockedResource|null findOneBy(array $criteria, array $orderBy = null)
 * @method LockedResource[]    findAll()
 * @method LockedResource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LockedResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LockedResource::class);
    }

    /**
     * @param LockedResource $locked_resource
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(LockedResource $locked_resource): void
    {
        $this->_em->persist($locked_resource);
        $this->_em->flush();
    }

    /**
     * @param LockedResource $locked_resource
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(LockedResource $locked_resource): void
    {
        $this->_em->remove($locked_resource);
        $this->_em->flush();
    }

    /**
     * Gets the LockedResource for entity name and record id
     * @param string $record
     * @param string $type
     * @param string $target
     * @return LockedResource|null
     */
    public function findOne(string $record, string $type, string $target):? LockedResource
    {
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder->select('lr')
            ->from(LockedResource::class, 'lr')
            ->where('lr.type = :type')
            ->andWhere('lr.record = :record')
            ->andWhere('lr.target = :target')
            ->setParameter("type", $type)
            ->setParameter("record", $record)
            ->setParameter("target", $target);

        $query   = $query_builder->getQuery();
        $results = $query->execute();

        if( empty($results) ){
            return null;
        }

        $record = reset($results);
        return $record;
    }

    /**
     * @param string $path
     * @return LockedResource|null
     */
    public function findByDirectoryLocation(string $path):? LockedResource
    {
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder->select('lr')
            ->from(LockedResource::class, 'lr')
            ->where('lr.type = :type')
            ->andWhere('lr.record = :path')
            ->setParameter("type", LockedResource::TYPE_DIRECTORY)
            ->setParameter("path", $path);

        $query   = $query_builder->getQuery();
        $results = $query->execute();

        if( empty($results) ){
            return null;
        }

        $record = reset($results);
        return $record;
    }

    /**
     * @param string $old_path
     * @param string $new_path
     */
    public function updatePath(string $old_path, string $new_path): void
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->update(LockedResource::class, 'lr')
            ->set('lr.record', ':new_path')
            ->where('lr.record = :old_path')
            ->setParameters([
               'new_path' => $new_path,
               'old_path' => $old_path,
            ]);

        $query = $qb->getQuery();
        $query->execute();
    }
}
