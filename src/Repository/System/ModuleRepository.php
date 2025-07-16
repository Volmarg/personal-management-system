<?php

namespace App\Repository\System;

use App\Entity\System\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Module|null find($id, $lockMode = null, $lockVersion = null)
 * @method Module|null findOneBy(array $criteria, array $orderBy = null)
 * @method Module[]    findAll()
 * @method Module[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    /**
     * Return single module entity by module name
     *
     * @param string $name
     * @param bool   $includeInactive
     *
     * @return Module|null
     */
    public function getOneByName(string $name, bool $includeInactive = false): ?Module
    {
        $params = [];
        if (!$includeInactive) {
            $params = ['active' => true];
        }

        return $this->findOneBy([
            'name' => $name,
            ...$params,
        ]);
    }
}
