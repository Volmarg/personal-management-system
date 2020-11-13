<?php

namespace App\Controller\Modules\Travels;

use App\Controller\Core\Application;
use App\Entity\Modules\Travels\MyTravelsIdeas;
use Doctrine\DBAL\DBALException;
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
     * @param bool $include_empty
     * @return mixed
     * @throws DBALException
     */
    public function getAllCategories(bool $include_empty = false){
        return $this->app->repositories->myTravelsIdeasRepository->getAllCategories($include_empty);
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

}
