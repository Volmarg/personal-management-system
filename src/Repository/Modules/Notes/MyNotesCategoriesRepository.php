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

}
