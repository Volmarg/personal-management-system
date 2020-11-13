<?php

namespace App\Repository\Modules\Achievements;

use App\Entity\Modules\Achievements\Achievement;
use App\Repository\AbstractRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Achievement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Achievement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Achievement[]    findAll()
 * @method Achievement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchievementRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Achievement::class);
    }

    /**
     * Will return all not deleted Achievements
     *
     * @return Achievement[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->findBy([AbstractRepository::FIELD_DELETED => 0]);
    }

    /**
     * Returns single entity found for given id or null if nothing was found
     *
     * @param int $id
     * @return Achievement|null
     */
    public function getOneById(int $id): ?Achievement
    {
        return $this->find($id);
    }

}
