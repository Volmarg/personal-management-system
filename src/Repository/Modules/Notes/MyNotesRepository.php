<?php

namespace App\Repository\Modules\Notes;

use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\System\LockedResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
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

    public function getCategories($all = false) {

        $categoriesWithNotes = '';

        //add counting of notes so if category has 0 notes then disable it

        if(!$all){
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
                            LEFT JOIN my_note mn_
                              ON mnc_.id = mn_.category_id
                            WHERE mnc_.parent_id = mnc.id
                              AND mnc_.parent_id IS NOT NULL
                              AND mnc_.deleted = 0
                              AND mn_.deleted  = 0
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
               LEFT JOIN my_note mn_
               ON mnc_.id = mn_.category_id
               WHERE mnc_.parent_id = mnc.id
               AND mnc_.parent_id IS NOT NULL
               AND mnc_.deleted = 0
               AND mn_.deleted  = 0
              ) AS childrens_id
          FROM my_note mn
          JOIN my_note_category mnc
            $categoriesWithNotes
            
            LEFT JOIN locked_resource lr
            ON  lr.record = mnc.id
            AND lr.target = :lock_target
            AND lr.type   = :lock_type            
            
          WHERE mn.deleted  = 0
            AND mnc.deleted = 0
            AND lr.id IS NULL            

          GROUP BY mnc.name
          ORDER BY -childrens_id DESC
        ";

        $params = [
            'lock_target' => ModulesController::MODULE_ENTITY_NOTES_CATEGORY,
            'lock_type'   => LockedResource::TYPE_ENTITY
        ];

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        $results = $statement->fetchAll();

        return (!empty($results) ? $results : []);
    }

    public function getNotesByCategory($category_id) {

        $sql = "
            SELECT mn.* 
            FROM my_note mn
            
            LEFT JOIN locked_resource lr
            ON  lr.record = :category_id
            AND lr.target = :lock_target
            AND lr.type   = :lock_type
            
            WHERE mn.category_id = :category_id
            AND mn.deleted <> 1
            AND lr.id IS NULL            
        ";

        $bindedValues = [
            'category_id' => $category_id,
            'lock_target' => ModulesController::MODULE_ENTITY_NOTES_CATEGORY,
            'lock_type'   => LockedResource::TYPE_ENTITY
        ];

        $statement    = $this->connection->prepare($sql);

        $statement->execute($bindedValues);
        $results = $statement->fetchAll();

        return (!empty($results) ? $results : []);
    }

    /**
     * @param int $category_id
     * @return false|mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function countNotesInCategoryByCategoryId(int $category_id) {

        $sql = "
            SELECT COUNT(*) 
            FROM my_note 
            
            LEFT JOIN locked_resource lr
            ON  lr.record = :category_id
            AND lr.target = :lock_target
            AND lr.type   = :lock_type            
            
            WHERE category_id = :category_id
            AND deleted = 0
            AND lr.id IS NULL            
            ";

        $params = [
            'category_id' => $category_id,
            'lock_target' => ModulesController::MODULE_ENTITY_NOTES_CATEGORY,
            'lock_type'   => LockedResource::TYPE_ENTITY
        ];

        $statement = $this->connection->executeQuery($sql, $params);
        $results = $statement->fetchColumn();

        return $results;
    }

}
