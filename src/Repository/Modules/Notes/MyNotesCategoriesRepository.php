<?php

namespace App\Repository\Modules\Notes;

use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyNotesCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyNotesCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyNotesCategories[]    findAll()
 * @method MyNotesCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyNotesCategoriesRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, MyNotesCategories::class);
    }

    /**
     * @return array|false|mixed
     * @throws DBALException
     */
    public function findActiveCategories($only_category = false) {
        $connection = $this->_em->getConnection();
        $icon       = (!$only_category ? ", mnc.icon AS icon"           : "");
        $color      = (!$only_category ? ", mnc.color AS color"         : "");
        $parent_id  = (!$only_category ? ", mnc.parent_id AS parent_id" : "");

        $sql = "
          SELECT 
            mnc.id AS id,
            mnc.name AS name 
            $icon
            $color
            $parent_id
          FROM my_note_category mnc
          WHERE mnc.deleted <> 1
          GROUP BY mnc.name;
        ";

        $statement = $connection->prepare($sql);
        $statement->execute();

        if ($icon) {
            $results = $statement->fetchAll();
        } else {
            $records = $statement->fetchAll();

            foreach ($records as $record) {
                $results[$record['name']] = $record['id'];
            }

        }

        return (!empty($results) ? $results : []);
    }

    /**
     * @param array $categories_ids
     * @return bool
     */
    public function haveCategoriesNotes(array $categories_ids): bool
    {
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder->select('mnc')
            ->from(MyNotesCategories::class, "mnc")
            ->join(MyNotes::class, 'mn', Join::WITH, "mn.category = mnc.id")
            ->where("mnc.id IN(:categoriesIds)")
            ->andWhere("mn.deleted = 0")
            ->setParameter("categoriesIds", $categories_ids, Connection::PARAM_STR_ARRAY);

        $query   = $query_builder->getQuery();
        $results = $query->execute();

        return !empty($results);
    }

    /**
     * @param array $categories_ids
     * @return bool
     */
    public function haveCategoriesChildren(array $categories_ids): bool
    {
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder->select('mnc_child')
            ->from(MyNotesCategories::class, "mnc")
            ->join(MyNotesCategories::class, 'mnc_child', Join::WITH, "mnc_child.parent_id = mnc.id")
            ->where("mnc.id IN (:categoriesIds)")
            ->andWhere("mnc_child.deleted = 0")
            ->setParameter("categoriesIds", $categories_ids);

        $query   = $query_builder->getQuery();
        $results = $query->execute();

        return !empty($results);
    }

    /**
     * @param array $categories_ids
     * @return MyNotesCategories[]
     */
    public function getChildrenCategoriesForCategoriesIds(array $categories_ids): array
    {
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder->select("mnc_child")
            ->from(MyNotesCategories::class, "mnc")
            ->join(MyNotesCategories::class, "mnc_child", Join::WITH, "mnc_child.parent = mnc.id")
            ->where("mnc.id IN (:categoriesIds)")
            ->andWhere("mnc_child.deleted = 0")
            ->setParameter("categoriesIds", $categories_ids);

        $query   = $query_builder->getQuery();
        $results = $query->execute();

        return $results;
    }

    /**
     * @param array $categories_ids
     * @return array
     */
    public function getChildrenCategoriesIdsForCategoriesIds(array $categories_ids): array
    {
        $query_builder = $this->_em->createQueryBuilder();

        $query_builder->select("mnc_child.id")
            ->from(MyNotesCategories::class, "mnc")
            ->join(MyNotesCategories::class, "mnc_child", Join::WITH, "mnc_child.parent_id = mnc.id")
            ->where("mnc.id IN (:categoriesIds)")
            ->andWhere("mnc_child.deleted = 0")
            ->setParameter("categoriesIds", $categories_ids);

        $query   = $query_builder->getQuery();
        $results = $query->execute();
        $ids     = array_column($results, 'id');

        return $ids;
    }

    /**
     * @param bool $only_categories_with_notes
     * @return array
     * @throws DBALException
     */
    public function getCategories(bool $only_categories_with_notes = false): array
    {
        $connection = $this->_em->getConnection();

        $categoriesWithNotes = '';

        //add counting of notes so if category has 0 notes then disable it

        if( $only_categories_with_notes ){
            $categoriesWithNotes = "
                -- get only categories with notes
                ON mn.category_id = mnc.id
                -- now additionally check if there are some categories with children that have active notes (need for menu)
                OR 
                (
                    SELECT GROUP_CONCAT(note.id) AS noteId
                    FROM my_note AS note
                    WHERE note.category_id IN 
                        (
                            SELECT DISTINCT mnc_.id
                            FROM my_note_category mnc_
                            WHERE mnc_.parent_id = mnc.id
                            AND mnc_.parent_id IS NOT NULL
                        )
                ) IS NOT NULL
            ";
        }

        $sql = "
          SELECT 
            mnc.name AS category,
            mnc.icon AS icon,
            mnc.color AS color,
            mnc.id AS category_id,
            mnc.parent_id AS parent_id,
             ( -- get children categories
               SELECT GROUP_CONCAT(DISTINCT mnc_.id)
               FROM my_note_category mnc_
               WHERE mnc_.parent_id = mnc.id
               AND mnc_.parent_id IS NOT NULL
              ) AS childrens_id
          FROM my_note mn
          JOIN my_note_category mnc
            $categoriesWithNotes

          WHERE mn.deleted  = 0
            AND mnc.deleted = 0

          GROUP BY mnc.name
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

}
