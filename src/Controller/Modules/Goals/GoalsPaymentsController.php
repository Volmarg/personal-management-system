<?php

namespace App\Controller\Modules\Goals;

use App\Controller\Core\Application;
use App\Entity\Modules\Goals\MyGoalsPayments;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GoalsPaymentsController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * Will return all not deleted entities
     *
     * @return MyGoalsPayments[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myGoalsPaymentsRepository->getAllNotDeleted();
    }

    /**
     * Will return single entity for given id, if none was found then null will be returned
     *
     * @param int $id
     * @return MyGoalsPayments|null
     */
    public function findOneById(int $id): ?MyGoalsPayments
    {
        return $this->app->repositories->myGoalsPaymentsRepository->findOneById($id);
    }

    /**
     * Will return one entity for given name if such exist, otherwise null is returned
     *
     * @param string $name
     * @return MyGoalsPayments|null
     */
    public function getOneByName(string $name): ?MyGoalsPayments
    {
        return $this->app->repositories->myGoalsPaymentsRepository->getOneByName($name);
    }

}
