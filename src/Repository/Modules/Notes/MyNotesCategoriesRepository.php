<?php

namespace App\Repository\Modules\Notes;

use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyNotesCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyNotesCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyNotesCategories[]    findAll()
 * @method MyNotesCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyNotesCategoriesRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, MyNotesCategories::class);
    }

    /**
     * @return Statement
     * @throws Exception
     */
    public function buildHaveCategoriesNotesStatement(): Statement
    {
        $connection = $this->_em->getConnection();

        $sql = "
            SELECT COUNT(id)
            FROM my_note
            
            WHERE 1
            AND category_id IN(?)
        ";

        $stmt = $connection->prepare($sql);

        return $stmt;
    }

    /**
     * @param Statement $statement
     * @param array $categoriesIds
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function executeHaveCategoriesNotesStatement(Statement $statement, array $categoriesIds): bool
    {
        $ids = "'" . implode("','", $categoriesIds) . "'";

        $statement->execute([$ids]);
        $result = $statement->fetchFirstColumn();

        if( empty($result) ){
            return false;
        }

        return true;
    }

    /**
     * @param array $categoriesIds
     * @return MyNotesCategories[]
     */
    public function getChildrenCategoriesForCategoriesIds(array $categoriesIds): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select("mnc_child")
            ->from(MyNotesCategories::class, "mnc")
            ->join(MyNotesCategories::class, "mnc_child", Join::WITH, "mnc_child.parentId = mnc.id")
            ->where("mnc.id IN (:categoriesIds)")
            ->andWhere("mnc_child.deleted = 0")
            ->setParameter("categoriesIds", $categoriesIds);

        $query   = $queryBuilder->getQuery();
        $results = $query->execute();

        return $results;
    }

    /**
     * @param array $categoriesIds
     * @return string[]
     */
    public function getChildrenCategoriesIdsForCategoriesIds(array $categoriesIds): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select("mnc_child.id")
            ->from(MyNotesCategories::class, "mnc")
            ->join(MyNotesCategories::class, "mnc_child", Join::WITH, "mnc_child.parentId = mnc.id")
            ->where("mnc.id IN (:categoriesIds)")
            ->andWhere("mnc_child.deleted = 0")
            ->setParameter("categoriesIds", $categoriesIds);

        $query   = $queryBuilder->getQuery();
        $results = $query->execute();
        $ids     = array_column($results, 'id');

        return $ids;
    }

    /**
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getCategories(): array
    {
        $connection = $this->_em->getConnection();

        $sql = "
            SELECT DISTINCT
                mnc.name               AS category,
                mnc.icon               AS icon,
                mnc.color              AS color,
                mnc.id                 AS category_id,
                mnc.parent_id          AS parent_id,
                childrens.childrens_id AS childrens_id
            FROM my_note_category mnc

            LEFT JOIN my_note mn
            ON  mn.category_id = mnc.id
            AND mn.deleted     = 0
                
            LEFT JOIN (
                SELECT 
                    GROUP_CONCAT(DISTINCT mnc_.id)  AS childrens_id,
                    mnc_.parent_id                  AS category_id
                
                FROM my_note_category mnc_
                
                GROUP BY mnc_.parent_id
            ) AS childrens
            ON childrens.category_id = mnc.id
            
            WHERE mnc.deleted  = 0
        ";

        $statement = $connection->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();

        return (!empty($results) ? $results : []);
    }

    /**
     * @return MyNotesCategories[]
     */
    public function findAllNotDeleted(): array
    {
        $entities = $this->findBy([MyNotesCategories::KEY_DELETED => 0]);
        return $entities;
    }

    /**
     * Returns categories inside given parentId
     * @param string $name
     * @param string $categoryId
     * @return MyNotesCategories[]
     */
    public function getNotDeletedCategoriesForParentIdAndName(string $name, ?string $categoryId): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select("mnc")
            ->from(MyNotesCategories::class, "mnc")
            ->where("mnc.deleted = 0");

        if( is_null($categoryId) ){
            $queryBuilder
                ->andWhere("mnc.name = :name")
                ->andWhere("mnc.parentId IS NULL")
                ->setParameters([
                    "name" => $name,
                ]);
        }else{
            $queryBuilder
                ->andWhere("mnc.name     = :name")
                ->andWhere("mnc.parentId = :categoryId")
                ->setParameters([
                    "name"       => $name,
                    "categoryId" => $categoryId,
                ]);
        }

        $query   = $queryBuilder->getQuery();
        $results = $query->execute();

        return $results;
    }

    /**
     * Will return one entity for given id, otherwise returns null if nothing is found
     *
     * @param int $id
     * @return MyNotesCategories|null
     */
    public function findOneById(int $id): ?MyNotesCategories
    {
        return $this->find($id);
    }

}
