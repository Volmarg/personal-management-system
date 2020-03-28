<?php

namespace App\Repository\Modules\Notes;

use App\Entity\Modules\Notes\MyNotes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MyNotes|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyNotes|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyNotes[]    findAll()
 * @method MyNotes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyNotesRepository extends ServiceEntityRepository {

    private $connection;

    public function __construct(RegistryInterface $registry, Connection $connection) {
        parent::__construct($registry, MyNotes::class);

        $this->connection = $connection;
    }

    /**
     * @param bool $only_categories_with_notes
     * @return array
     * @throws DBALException
     */
    public function getCategories(bool $only_categories_with_notes = false): array
    {

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
          ORDER BY -childrens_id DESC
        ";

        $statement = $this->connection->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();

        return (!empty($results) ? $results : []);
    }

    /**
     * @param array $categories_ids
     * @return MyNotes[]
     */
    public function getNotesByCategory(array $categories_ids): array
    {
        $results = $this->findBy([
            'category' => $categories_ids,
            "deleted"  => 0
        ]);

        return $results;
    }

    /**
     * @param int $category_id
     * @return false|mixed
     * @throws DBALException
     */
    public function countNotesInCategoryByCategoryId(int $category_id) {

        $sql = "SELECT COUNT(*) FROM my_note WHERE category_id = :category_id AND deleted = 0";

        $params = [
            'category_id' => $category_id,
        ];

        $statement = $this->connection->executeQuery($sql, $params);
        $results = $statement->fetchColumn();

        return $results;
    }

}
