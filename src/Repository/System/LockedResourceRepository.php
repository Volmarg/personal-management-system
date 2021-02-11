<?php

namespace App\Repository\System;

use App\Entity\System\LockedResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Statement;
use Doctrine\Persistence\ManagerRegistry;
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
     * @param LockedResource $lockedResource
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(LockedResource $lockedResource): void
    {
        $this->_em->persist($lockedResource);
        $this->_em->flush();
    }

    /**
     * @param LockedResource $lockedResource
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(LockedResource $lockedResource): void
    {
        $this->_em->remove($lockedResource);
        $this->_em->flush();
    }

    /**
     * Gets the LockedResource for entity name and record id
     * @param string $record
     * @param string $type
     * @param string $target
     * @return LockedResource|null
     */
    public function findOneEntity(string $record, string $type, string $target):? LockedResource
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select('lr')
            ->from(LockedResource::class, 'lr')
            ->where('lr.type = :type')
            ->andWhere('lr.record = :record')
            ->andWhere('lr.target = :target')
            ->setParameter("type", $type)
            ->setParameter("record", $record)
            ->setParameter("target", $target);

        $query   = $queryBuilder->getQuery();
        $results = $query->execute();

        if( empty($results) ){
            return null;
        }

        $record = reset($results);
        return $record;
    }

    /**
     * Returns executable statement for information (bool) weather there is lock entry in DB for given:
     * - record,
     * - type,
     * - target,
     *
     * Solution with building statement at first was implemented due to need of reducing sqls call time
     *
     * @return Statement
     * @throws \Doctrine\DBAL\Exception
     */
    public function buildIsLockForRecordTypeAndTargetStatement()
    {
        $connection = $this->_em->getConnection();

        $sql = "
            SELECT id
            
            FROM locked_resource
            
            WHERE 1
            AND type   = ?
            AND target = ?
            AND record = ?
        ";

        $stmt  = $connection->prepare($sql);

        return $stmt;
    }

    /**
     * Returns information (bool) weather there is lock entry in DB for given:
     * - record,
     * - type,
     * - target,
     *
     * @param Statement $stmt
     * @param string $record
     * @param string $type
     * @param string $target
     * @return bool
     * @throws Exception
     */
    public function executeIsLockForRecordTypeAndTargetStatement(Statement $stmt, string $record, string $type, string $target): bool
    {
        $params = [
            $type,
            $target,
            $record,
        ];

        $stmt->execute($params);
        $result = $stmt->fetchFirstColumn();

        return !empty($result);
    }

    /**
     * @param string $path
     * @return LockedResource|null
     */
    public function findByDirectoryLocation(string $path):? LockedResource
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select('lr')
            ->from(LockedResource::class, 'lr')
            ->where('lr.type = :type')
            ->andWhere('lr.record = :path')
            ->setParameter("type", LockedResource::TYPE_DIRECTORY)
            ->setParameter("path", $path);

        $query   = $queryBuilder->getQuery();
        $results = $query->execute();

        if( empty($results) ){
            return null;
        }

        $record = reset($results);
        return $record;
    }

    /**
     * @param string $oldPath
     * @param string $newPath
     */
    public function updatePath(string $oldPath, string $newPath): void
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->update(LockedResource::class, 'lr')
            ->set('lr.record', ':new_path')
            ->where('lr.record = :old_path')
            ->setParameters([
               'new_path' => $newPath,
               'old_path' => $oldPath,
            ]);

        $query = $qb->getQuery();
        $query->execute();
    }
}
