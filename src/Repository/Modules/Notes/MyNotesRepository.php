<?php

namespace App\Repository\Modules\Notes;

use App\Entity\Modules\Notes\MyNotes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyNotes|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyNotes|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyNotes[]    findAll()
 * @method MyNotes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyNotesRepository extends ServiceEntityRepository {

    private $connection;

    public function __construct(ManagerRegistry $registry, Connection $connection) {
        parent::__construct($registry, MyNotes::class);

        $this->connection = $connection;
    }

    /**
     * @param array $categoriesIds
     * @return MyNotes[]
     */
    public function getNotesByCategoriesIds(array $categoriesIds): array
    {
        $results = $this->findBy([
            'category' => $categoriesIds,
            "deleted"  => 0
        ]);

        return $results;
    }

    /**
     * @param int $categoryId
     * @return false|mixed
     * @throws DBALException
     */
    public function countNotesInCategoryByCategoryId(int $categoryId) {

        $sql = "SELECT COUNT(*) FROM my_note WHERE category_id = :category_id AND deleted = 0";

        $params = [
            'category_id' => $categoryId,
        ];

        $statement = $this->connection->executeQuery($sql, $params);
        $results = $statement->fetchColumn();

        return $results;
    }

    /**
     * Returns one note for given id or null if nothing was found
     * @param int $id
     * @return MyNotes|null
     */
    public function getOneById(int $id): ?MyNotes
    {
        return $this->find($id);
    }

    /**
     * Will return all not deleted entities
     *
     * @return MyNotes[]
     */
    public function findAllNotDeleted(): array
    {
        $entities = $this->findBy([MyNotes::KEY_DELETED => 0]);
        return $entities;
    }

}
