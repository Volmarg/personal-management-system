<?php

namespace App\Repository\Modules\Todo;

use App\Entity\Modules\Todo\MyTodoElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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

    /**
     * @param MyTodoElement $todo_element
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(MyTodoElement $todo_element): void
    {
        $this->_em->persist($todo_element);
        $this->_em->flush();
    }

}
