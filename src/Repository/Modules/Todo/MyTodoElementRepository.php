<?php

namespace App\Repository\Modules\Todo;

use App\Entity\Modules\Todo\MyTodoElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MyTodoElement|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyTodoElement|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyTodoElement[]    findAll()
 * @method MyTodoElement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyTodoElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyTodoElement::class);
    }

}
