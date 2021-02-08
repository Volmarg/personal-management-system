<?php

namespace App\Controller\Modules\Travels;

use App\Controller\Core\Application;
use App\Entity\Modules\Travels\MyTravelsIdeas;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyTravelsIdeasController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @param bool $includeEmpty
     * @return mixed
     * @throws DBALException
     */
    public function getAllCategories(bool $includeEmpty = false){
        return $this->app->repositories->myTravelsIdeasRepository->getAllCategories($includeEmpty);
    }

    /**
     * Will return one entity for id, if none is found then null is returned
     *
     * @param int $id
     * @return MyTravelsIdeas|null
     */
    public function findOneById(int $id): ?MyTravelsIdeas
    {
        return $this->app->repositories->myTravelsIdeasRepository->findOneById($id);
    }

    /**
     * Will return all not deleted entities
     *
     * @return MyTravelsIdeas[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myTravelsIdeasRepository->getAllNotDeleted();
    }

    /**
     * Will save the entity in the database
     *
     * @param MyTravelsIdeas $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(MyTravelsIdeas $entity): void
    {
        $this->app->repositories->myTravelsIdeasRepository->save($entity);;
    }

}
