<?php

namespace App\Repository\Modules\Issues;

use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Issues\MyIssueContact;
use App\Entity\Modules\Issues\MyIssueProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyIssue|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyIssue|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyIssue[]    findAll()
 * @method MyIssue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyIssueRepository extends ServiceEntityRepository
{
    const MY_ISSUE_TABLE_ALIAS = "mi";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyIssue::class);
    }

    /**
     * @param int|null $orderByFieldEntityId
     * @return MyIssue[]
     */
    public function findAllNotDeletedAndNotResolved(int $orderByFieldEntityId = null): array
    {
        $alias        = self::MY_ISSUE_TABLE_ALIAS;
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder
            ->select($alias)
            ->from(MyIssue::class, $alias);

        $queryBuilder = $this->filterQueryBuilderResultByNotDeletedAndNotResolved($queryBuilder, $alias, false);

        $query   = $queryBuilder->getQuery();
        $results = $query->execute();

        if( !empty($orderByFieldEntityId) ){
            $newResults = [];

            /**
             * Query builder does not support order by FIELD
             * @var MyIssue $entity
             */
            foreach( $results as $entity ){
                if( $orderByFieldEntityId === $entity->getId() ){
                    array_unshift($newResults, $entity);
                }else{
                    $newResults[] = $entity;
                }
            }

            return $newResults;
        }

        return $results;
    }

    /**
     * @return MyIssue[]
     */
    public function findAllNotDeletedAndResolved(): array
    {
        $alias        = self::MY_ISSUE_TABLE_ALIAS;
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select($alias)
            ->from(MyIssue::class, $alias);

        $queryBuilder = $this->filterQueryBuilderResultByNotDeletedAndResolved($queryBuilder, $alias, false);

        $query   = $queryBuilder->getQuery();
        $results = $query->execute();

        return $results;
    }

    /**
     * @param MyIssue $issue
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveIssue(MyIssue $issue)
    {
        $this->_em->persist($issue);
        $this->_em->flush();
    }

    /**
     * @param MyIssueContact $myIssueContact
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveIssueContact(MyIssueContact $myIssueContact): void
    {
        $this->_em->persist($myIssueContact);
        $this->_em->flush();
    }

    /**
     * @param MyIssueProgress $myIssueProgress
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveIssueProgress(MyIssueProgress $myIssueProgress): void
    {
        $this->_em->persist($myIssueProgress);
        $this->_em->flush();
    }

    /**
     * @return MyIssue[]
     */
    public function getPendingIssuesForDashboard(): array
    {
        $results = $this->findBy([
            'showOnDashboard' => 1,
            'deleted'         => 0
        ]);
        return $results;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $tableAlias
     * @param bool $isAnd
     * @return QueryBuilder
     */
    private function filterQueryBuilderResultByNotDeletedAndNotResolved(QueryBuilder $queryBuilder, string $tableAlias, bool $isAnd = true): QueryBuilder
    {
        if($isAnd){
            $queryBuilder->andWhere("{$tableAlias}." . MyIssue::FIELD_NAME_DELETED  . " = 0");
        }else{
            $queryBuilder->where("{$tableAlias}." . MyIssue::FIELD_NAME_DELETED  . " = 0");
        }

        $queryBuilder->andWhere("{$tableAlias}." . MyIssue::FIELD_NAME_RESOLVED . " = 0");
        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $tableAlias
     * @param bool $isAnd
     * @return QueryBuilder
     */
    private function filterQueryBuilderResultByNotDeletedAndResolved(QueryBuilder $queryBuilder, string $tableAlias, bool $isAnd = true): QueryBuilder
    {
        if($isAnd){
            $queryBuilder->andWhere("{$tableAlias}." . MyIssue::FIELD_NAME_DELETED  . " = 0");
        }else{
            $queryBuilder->where("{$tableAlias}." . MyIssue::FIELD_NAME_DELETED  . " = 0");
        }

        $queryBuilder->andWhere("{$tableAlias}." . MyIssue::FIELD_NAME_RESOLVED . " = 1");
        return $queryBuilder;
    }

    /**
     * Returns one Entity or null for given id
     * @param int $entityId
     * @return MyIssue|null
     */
    public function findIssueById(int $entityId): ?MyIssue
    {
        return $this->find($entityId);
    }
}
