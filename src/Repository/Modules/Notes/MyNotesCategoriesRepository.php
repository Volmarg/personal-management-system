<?php

namespace App\Repository\Modules\Notes;

use App\Controller\Modules\ModulesController;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Entity\System\LockedResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @return MyNotesCategories[]
     */
    public function getNotDeletedAndNotLocked(): array
    {
        // todo -> 2108
        $queryBuilder = $this->_em->createQueryBuilder('mnc');

        $queryBuilder->select('mnc')
            ->from(MyNotesCategories::class, 'mnc')
            ->leftJoin(LockedResource::class, 'lr', Join::WITH, $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('lr.record', 'mnc.id'),
                $queryBuilder->expr()->eq('lr.target', ':lock_target'),
                $queryBuilder->expr()->eq('lr.type', ':lock_type')
            ))
            ->where('mnc.deleted = 0')
            ->andWhere('lr.id IS NULL')
            ->setParameter('lock_target', ModulesController::MODULE_ENTITY_NOTES_CATEGORY)
            ->setParameter('lock_type', LockedResource::TYPE_ENTITY);

        $query   = $queryBuilder->getQuery();
        $results = $query->execute();

        return $results;
    }


}
