<?php

namespace App\Repository\Modules\Notes;

use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
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
     * @throws \Doctrine\DBAL\DBALException
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
}
