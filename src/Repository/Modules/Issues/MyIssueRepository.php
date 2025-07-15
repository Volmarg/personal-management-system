<?php

namespace App\Repository\Modules\Issues;

use App\Entity\Modules\Issues\MyIssue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @param bool  $includeDeleted
     * @param array $includedIds
     *
     * @return array
     */
    public function findAllAssignable(bool $includeDeleted = false, array $includedIds = []): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("mi")
            ->from(MyIssue::class, "mi")
            ->where("1=1");

        if (!$includeDeleted) {
            $qb->andWhere("mi.deleted = 0");
        }

        if (!empty($includedIds)) {
            $qb->andWhere($qb->expr()->orX(
                'mi.todo IS NULL',
                'mi.id IN (:includedIds)'
            ))->setParameter('includedIds', $includedIds);
        } else {
            $qb->andWhere('mi.todo IS NULL');
        }

        return $qb->getQuery()->execute();
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

}
