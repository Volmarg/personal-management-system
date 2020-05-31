<?php

namespace App\Repository\Modules\Issues;

use App\Entity\Modules\Issues\MyIssue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyIssue|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyIssue|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyIssue[]    findAll()
 * @method MyIssue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyIssueRepository extends ServiceEntityRepository
{
    const MY_ISSUE_TABLE_ALIAS = "mi";

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyIssue::class);
    }

    /**
     * @param int|null $order_by_field_entity_id
     * @return MyIssue[]
     */
    public function findAllNotDeletedAndNotResolved(int $order_by_field_entity_id = null): array
    {
        $alias         = self::MY_ISSUE_TABLE_ALIAS;
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder
            ->select($alias)
            ->from(MyIssue::class, $alias);

        $query_builder = $this->filterQueryBuilderResultByNotDeletedAndNotResolved($query_builder, $alias, false);

        $query   = $query_builder->getQuery();
        $results = $query->execute();

        if( !empty($order_by_field_entity_id) ){
            $new_results = [];

            /**
             * Query builder does not support order by FIELD
             * @var MyIssue $entity
             */
            foreach( $results as $entity ){
                if( $order_by_field_entity_id === $entity->getId() ){
                    array_unshift($new_results, $entity);
                }else{
                    $new_results[] = $entity;
                }
            }

            return $new_results;
        }

        return $results;
    }

    /**
     * @return MyIssue[]
     */
    public function findAllNotDeletedAndResolved(): array
    {
        $alias         = self::MY_ISSUE_TABLE_ALIAS;
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder->select($alias)
            ->from(MyIssue::class, $alias);

        $query_builder = $this->filterQueryBuilderResultByNotDeletedAndResolved($query_builder, $alias, false);

        $query   = $query_builder->getQuery();
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
        $this->_em->flush($issue);
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
     * @param QueryBuilder $query_builder
     * @param string $tableAlias
     * @param bool $isAnd
     * @return QueryBuilder
     */
    private function filterQueryBuilderResultByNotDeletedAndNotResolved(QueryBuilder $query_builder, string $tableAlias, bool $isAnd = true): QueryBuilder
    {
        if($isAnd){
            $query_builder->andWhere("{$tableAlias}." . MyIssue::FIELD_NAME_DELETED  . " = 0");
        }else{
            $query_builder->where("{$tableAlias}." . MyIssue::FIELD_NAME_DELETED  . " = 0");
        }

        $query_builder->andWhere("{$tableAlias}." . MyIssue::FIELD_NAME_RESOLVED . " = 0");
        return $query_builder;
    }

    /**
     * @param QueryBuilder $query_builder
     * @param string $tableAlias
     * @param bool $isAnd
     * @return QueryBuilder
     */
    private function filterQueryBuilderResultByNotDeletedAndResolved(QueryBuilder $query_builder, string $tableAlias, bool $isAnd = true): QueryBuilder
    {
        if($isAnd){
            $query_builder->andWhere("{$tableAlias}." . MyIssue::FIELD_NAME_DELETED  . " = 0");
        }else{
            $query_builder->where("{$tableAlias}." . MyIssue::FIELD_NAME_DELETED  . " = 0");
        }

        $query_builder->andWhere("{$tableAlias}." . MyIssue::FIELD_NAME_RESOLVED . " = 1");
        return $query_builder;
    }

}
