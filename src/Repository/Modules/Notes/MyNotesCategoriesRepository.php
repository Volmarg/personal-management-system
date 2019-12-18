<?php

namespace App\Repository\Modules\Notes;

use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

}
