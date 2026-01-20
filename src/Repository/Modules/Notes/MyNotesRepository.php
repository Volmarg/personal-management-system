<?php

namespace App\Repository\Modules\Notes;

use App\Entity\Modules\Notes\MyNotes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
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
     * Will return all not deleted entities
     *
     * @return MyNotes[]
     */
    public function findAllNotDeleted(): array
    {
        $entities = $this->findBy(["deleted" => 0]);
        return $entities;
    }

    /**
     * @param string $title
     *
     * @return array
     */
    public function findByTitle(string $title): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("n")
            ->from(MyNotes::class, "n")
            ->where(
                $qb->expr()->like("n.Title", ":title"),
                $qb->expr()->neq("n.deleted", 1),
            )
            ->setParameter("title" ,"%{$title}%");

        return $qb->getQuery()->getResult();
    }

}
