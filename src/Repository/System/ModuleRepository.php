<?php

namespace App\Repository\System;

use App\Entity\Modules\Todo\MyTodo;
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
     * Will return all active modules;
     *
     * @return Module[]
     */
    public function getAllActive(): array
    {
        $modules = $this->findBy([
            Module::FIELD_ACTIVE => true
        ]);

        return $modules;
    }

    /**
     * Return single module entity by module name
     *
     * @param string $name
     * @return Module
     */
    public function getOneByName(string $name): Module
    {
        return $this->findOneBy([Module::FIELD_NAME => $name]);
    }
}
